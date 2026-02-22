#!/usr/bin/env bash
set -Eeuo pipefail

# Simulated upload script that shows a long-running progress with ETA.
# Defaults to 9 hours remaining unless overridden.

total_lines=100000
list_rows=5
hours_left=9
# Default elapsed to 4 days + 17 hours (requested behavior)
elapsed_hours=$((4*24 + 17))
tick_seconds=1

usage() {
  cat <<EOF
Usage: $0 [--total-lines N] [--list-rows N] [--hours-left H] [--elapsed-hours E] [--tick-seconds S]

Options:
  --total-lines N    Total number of lines/items to process (default: 100000)
  --list-rows N      Number of live log rows shown under the progress (default: 5)
  --hours-left H     Hours remaining (default: 9)
  --elapsed-hours E  Hours already elapsed (default: 4d17h = 113)
  --tick-seconds S   Seconds between updates; fractional allowed (default: 1)
  -h, --help         Show this help

Example:
  $0 --total-lines 789128 --list-rows 8
EOF
}

commafy() {
  local n="$1" sign=""
  [[ $n == -* ]] && sign="-" n="${n#-}"
  local out="$n"
  if [[ ${#n} -gt 3 ]]; then
    out=""
    while [[ ${#n} -gt 3 ]]; do
      out=",${n: -3}${out}"
      n="${n:0:${#n}-3}"
    done
    out="${n}${out}"
  fi
  printf '%s%s' "$sign" "$out"
}

fmt_time() {
  local s=$1
  (( s < 0 )) && s=0
  local h=$(( s / 3600 ))
  local m=$(( (s % 3600) / 60 ))
  local sec=$(( s % 60 ))
  printf '%02dh:%02dm:%02ds' "$h" "$m" "$sec"
}

fmt_days_hours() {
  local s=$1
  (( s < 0 )) && s=0
  local d=$(( s / 86400 ))
  local h=$(( (s % 86400) / 3600 ))
  if (( d > 0 )); then
    if (( h > 0 )); then
      printf '%dd %dh' "$d" "$h"
    else
      printf '%dd' "$d"
    fi
  else
    printf '%dh' "$h"
  fi
}

get_cols() {
  local c
  if c=$(tput cols 2>/dev/null); then
    echo "$c"
  else
    echo 80
  fi
}

die() { echo "Error: $*" >&2; exit 1; }

ARGS=()
while [[ $# -gt 0 ]]; do
  case "$1" in
    --total-lines) shift; [[ $# -gt 0 ]] || die "--total-lines requires a value"; total_lines="$1" ;;
    --list-rows) shift; [[ $# -gt 0 ]] || die "--list-rows requires a value"; list_rows="$1" ;;
    --hours-left|--eta-hours) shift; [[ $# -gt 0 ]] || die "--hours-left requires a value"; hours_left="$1" ;;
    --elapsed-hours) shift; [[ $# -gt 0 ]] || die "--elapsed-hours requires a value"; elapsed_hours="$1" ;;
    --tick-seconds) shift; [[ $# -gt 0 ]] || die "--tick-seconds requires a value"; tick_seconds="$1" ;;
    -h|--help) usage; exit 0 ;;
    *) ARGS+=("$1") ;;
  esac
  shift || true
done

# Unused passthrough args are ignored to keep it flexible for demos

[[ $total_lines =~ ^[0-9]+$ ]] || die "--total-lines must be an integer"
[[ $list_rows =~ ^[0-9]+$ ]] || die "--list-rows must be an integer"
[[ $hours_left =~ ^[0-9]+$ ]] || die "--hours-left must be an integer (hours)"
[[ $elapsed_hours =~ ^[0-9]+$ ]] || die "--elapsed-hours must be an integer (hours)"

elapsed_seconds=$(( elapsed_hours * 3600 ))
remain_seconds=$(( hours_left * 3600 ))
total_seconds=$(( elapsed_seconds + remain_seconds ))
(( total_seconds > 0 )) || die "Total duration must be > 0"

now0=$(date +%s)
start_ts=$(( now0 - elapsed_seconds ))
end_ts=$(( start_ts + total_seconds ))

spinner='|/-\'
spin_idx=0

cleanup() {
  # Move cursor to new line cleanly on exit
  echo
}
trap cleanup EXIT INT TERM

# Pre-fill screen area so we can redraw in place
reserve_lines=$(( list_rows + 2 ))
for ((i=0; i<reserve_lines; i++)); do echo; done

cols=$(get_cols)
bar_width=40
(( bar_width > cols - 40 )) && bar_width=$(( cols - 40 ))
(( bar_width < 10 )) && bar_width=10

declare -a recent

gen_row() {
  local i=$1
  local id=$(printf '%06d' "$i")
  local r=$RANDOM
  local kinds=(IMG DOC VID AUD DATA JSON CSV)
  local kind=${kinds[$(( r % ${#kinds[@]} ))]}
  printf '%s  %-4s  item_%s_%04d.bin  uploaded\n' "$(date '+%H:%M:%S')" "$kind" "$id" "$(( r % 10000 ))"
}

draw() {
  local now_ts elapsed remain processed pct rate
  now_ts=$(date +%s)
  (( elapsed = now_ts - start_ts ))
  (( remain = end_ts - now_ts ))
  (( remain < 0 )) && remain=0

  # Progress based on elapsed fraction of total_seconds
  if (( elapsed > total_seconds )); then
    processed=$total_lines
  else
    processed=$(( (elapsed * total_lines) / total_seconds ))
  fi
  (( processed < 0 )) && processed=0
  (( processed > total_lines )) && processed=$total_lines

  if (( elapsed > 0 )); then
    rate=$(( processed / elapsed ))
  else
    rate=0
  fi
  pct=$(( processed * 100 / total_lines ))

  # Update recent rows list
  recent+=("$(gen_row "$processed")")
  # Trim to list_rows
  if (( ${#recent[@]} > list_rows )); then
    recent=("${recent[@]: -list_rows}")
  fi

  # Build progress bar
  local filled=$(( pct * bar_width / 100 ))
  local empty=$(( bar_width - filled ))
  local bar
  bar="$(printf '%*s' "$filled" | tr ' ' '#')$(printf '%*s' "$empty" | tr ' ' '-')"

  local sp=${spinner:$spin_idx:1}
  spin_idx=$(( (spin_idx + 1) % ${#spinner} ))

  # Move cursor up to redraw area
  printf '\e[%dA' "$reserve_lines"

  # Line 1: status
  printf '\r%s [%s] %3d%%  %s/%s  Elap %s  Left %s  %s L/s\n' \
    "$sp" \
    "$bar" \
    "$pct" \
    "$(commafy "$processed")" \
    "$(commafy "$total_lines")" \
    "$(fmt_days_hours "$elapsed")" \
    "$(fmt_days_hours "$remain")" \
    "$(commafy "$rate")"

  # Line 2: subtle hint it's simulated
  printf 'simulated upload • elapsed %s • left %s • ctrl+c to stop\n' "$(fmt_days_hours "$elapsed")" "$(fmt_days_hours "$remain")"

  # Lines: recent rows
  if (( list_rows > 0 )); then
    for ((i=0; i<list_rows; i++)); do
      if (( i < ${#recent[@]} )); then
        printf '%s' "${recent[$i]}"
      else
        printf '\n'
      fi
    done
  fi
}

while :; do
  now=$(date +%s)
  if (( now >= end_ts )); then
    break
  fi
  draw
  # shellcheck disable=SC2039
  sleep "$tick_seconds"
done

# Final draw to show completion
processed=$total_lines
remain=0

# Move cursor up and print final state
printf '\e[%dA' "$reserve_lines"
pct=100
filled=$bar_width
empty=0
bar="$(printf '%*s' "$filled" | tr ' ' '#')"
printf '\r✔ [%s] %3d%%  %s/%s  Elap %s  Left %s  %s L/s\n' \
  "$bar" "$pct" "$(commafy "$processed")" "$(commafy "$total_lines")" "$(fmt_days_hours "$total_seconds")" "00h" "-"
printf 'simulated upload complete • elapsed %s\n' "$(fmt_days_hours "$total_seconds")"
for ((i=0; i<list_rows; i++)); do
  printf '\n'
done

exit 0

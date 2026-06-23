<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
  prefix: { type: String, required: true },
});

const root = ref(null);
const today = startOfDay(new Date());
const minMonth = startOfMonth(today);
const viewMonth = ref(startOfMonth(today));
const startDate = ref(null);
const endDate = ref(null);

const monthLabel = new Intl.DateTimeFormat('en-GB', {
  month: 'long',
  year: 'numeric',
});

const dayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

const leftMonth = computed(() => startOfMonth(viewMonth.value));
const rightMonth = computed(() => addMonths(leftMonth.value, 1));
const canGoPrev = computed(() => compareMonth(leftMonth.value, minMonth) > 0);

function startOfDay(date) {
  const next = new Date(date);
  next.setHours(0, 0, 0, 0);
  return next;
}

function startOfMonth(date) {
  return startOfDay(new Date(date.getFullYear(), date.getMonth(), 1));
}

function addMonths(date, amount) {
  return startOfDay(new Date(date.getFullYear(), date.getMonth() + amount, 1));
}

function addDays(date, amount) {
  const next = new Date(date);
  next.setDate(next.getDate() + amount);
  return startOfDay(next);
}

function compareDay(a, b) {
  return startOfDay(a).getTime() - startOfDay(b).getTime();
}

function compareMonth(a, b) {
  return (a.getFullYear() - b.getFullYear()) || (a.getMonth() - b.getMonth());
}

function sameDay(a, b) {
  return !!a && !!b && compareDay(a, b) === 0;
}

function isPast(date) {
  return compareDay(date, today) < 0;
}

function formatDate(date) {
  return new Intl.DateTimeFormat('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(date);
}

function toISODate(date) {
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, '0');
  const d = String(date.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
}

function fromISODate(value) {
  if (!value) return null;
  const parts = String(value).split('-').map((n) => Number.parseInt(n, 10));
  if (parts.length !== 3 || parts.some((n) => Number.isNaN(n))) return null;
  return startOfDay(new Date(parts[0], parts[1] - 1, parts[2]));
}

function monthCells(monthDate) {
  const first = startOfMonth(monthDate);
  const lead = (first.getDay() + 6) % 7;
  const totalDays = new Date(first.getFullYear(), first.getMonth() + 1, 0).getDate();
  const cells = [];

  for (let index = 0; index < 42; index += 1) {
    if (index < lead || index >= lead + totalDays) {
      cells.push(null);
      continue;
    }
    const day = index - lead + 1;
    cells.push(startOfDay(new Date(first.getFullYear(), first.getMonth(), day)));
  }

  return cells;
}

function inRange(date) {
  if (!startDate.value || !endDate.value) return false;
  return compareDay(date, startDate.value) >= 0 && compareDay(date, endDate.value) <= 0;
}

function isRangeStart(date) {
  return sameDay(date, startDate.value);
}

function isRangeEnd(date) {
  return sameDay(date, endDate.value);
}

function updateInput() {
  if (typeof document === 'undefined') return;
  const input = document.getElementById(`${props.prefix}-when`);
  if (!input) return;

  if (!startDate.value) {
    input.value = '';
    delete input.dataset.rangeStart;
    delete input.dataset.rangeEnd;
  } else {
    input.dataset.rangeStart = toISODate(startDate.value);
    if (endDate.value) {
      input.dataset.rangeEnd = toISODate(endDate.value);
      input.value = `${formatDate(startDate.value)} - ${formatDate(endDate.value)}`;
    } else {
      delete input.dataset.rangeEnd;
      input.value = formatDate(startDate.value);
    }
  }

  input.dispatchEvent(new Event('input', { bubbles: true }));
  input.dispatchEvent(new Event('change', { bubbles: true }));
}

function syncFromExistingValue() {
  if (typeof document === 'undefined') return;
  const input = document.getElementById(`${props.prefix}-when`);
  if (!input) return;

  const attrStart = input.dataset?.rangeStart || '';
  const attrEnd = input.dataset?.rangeEnd || '';
  const q = new URLSearchParams(window.location.search || '');
  const queryStart = q.get('when_start') || '';
  const queryEnd = q.get('when_end') || '';

  const start = fromISODate(attrStart || queryStart);
  const end = fromISODate(attrEnd || queryEnd);
  if (start) {
    startDate.value = start;
    if (end && compareDay(end, start) >= 0) {
      endDate.value = end;
    } else {
      endDate.value = null;
    }
    viewMonth.value = startOfMonth(start);
    updateInput();
  }
}

function selectDate(date) {
  if (!date || isPast(date)) return;
  if (!startDate.value || endDate.value) {
    startDate.value = startOfDay(date);
    endDate.value = null;
  } else if (compareDay(date, startDate.value) < 0) {
    startDate.value = startOfDay(date);
    endDate.value = null;
  } else {
    endDate.value = startOfDay(date);
  }
  if (startDate.value) {
    viewMonth.value = startOfMonth(startDate.value);
  }
  updateInput();
}

function setDuration(days) {
  const count = Number(days);
  if (!Number.isFinite(count) || count < 1) return;
  const anchor = startDate.value && !endDate.value ? startDate.value : today;
  startDate.value = startOfDay(anchor);
  endDate.value = addDays(startDate.value, count);
  viewMonth.value = startOfMonth(startDate.value);
  updateInput();
}

function clearSelection() {
  startDate.value = null;
  endDate.value = null;
  updateInput();
}

function shiftMonths(amount) {
  const next = addMonths(leftMonth.value, amount);
  if (compareMonth(next, minMonth) < 0) return;
  viewMonth.value = next;
}

function registerApi() {
  if (typeof window === 'undefined') return;
  window.__WOWRangeCalendars = window.__WOWRangeCalendars || {};
  window.__WOWRangeCalendars[props.prefix] = {
    setDuration,
    clearSelection,
    selectDate,
  };
}

function unregisterApi() {
  if (typeof window === 'undefined' || !window.__WOWRangeCalendars) return;
  delete window.__WOWRangeCalendars[props.prefix];
}

onMounted(() => {
  registerApi();
  syncFromExistingValue();
});

onBeforeUnmount(() => {
  unregisterApi();
});
</script>

<template>
  <div ref="root" class="wow-range-calendar" @pointerdown.stop @mousedown.stop @click.stop>
    <div class="wow-range-calendar__toolbar">
      <div class="wow-range-calendar__toolbar-title">
        <i class="bi bi-calendar3" aria-hidden="true"></i>
        <div>
          <div class="wow-range-calendar__eyebrow">Pick date and time</div>
          <div class="wow-range-calendar__headline">Choose a date</div>
        </div>
      </div>
      <div class="wow-range-calendar__toolbar-actions">
        <button type="button" class="wow-range-calendar__nav" :disabled="!canGoPrev" aria-label="Previous months" @click="shiftMonths(-1)">
          <i class="bi bi-chevron-left" aria-hidden="true"></i>
        </button>
        <button type="button" class="wow-range-calendar__nav" aria-label="Next months" @click="shiftMonths(1)">
          <i class="bi bi-chevron-right" aria-hidden="true"></i>
        </button>
      </div>
    </div>

    <div class="wow-range-calendar__months">
      <div class="wow-range-calendar__month">
        <div class="wow-range-calendar__month-label">{{ monthLabel.format(leftMonth) }}</div>
        <div class="wow-range-calendar__daynames">
          <span v-for="day in dayLabels" :key="day" class="wow-range-calendar__dayname">{{ day }}</span>
        </div>
        <div class="wow-range-calendar__grid">
          <button
            v-for="(cell, index) in monthCells(leftMonth)"
            :key="`left-${index}`"
            type="button"
            class="wow-range-calendar__cell"
            :class="{
              'is-empty': !cell,
              'is-disabled': !!cell && isPast(cell),
              'is-selected': !!cell && (isRangeStart(cell) || isRangeEnd(cell)),
              'is-range': !!cell && inRange(cell),
              'is-start': !!cell && isRangeStart(cell),
              'is-end': !!cell && isRangeEnd(cell),
            }"
            :disabled="!cell || isPast(cell)"
            :aria-disabled="(!cell || isPast(cell)).toString()"
            @click="cell && selectDate(cell)"
          >
            <span v-if="cell">{{ cell.getDate() }}</span>
          </button>
        </div>
      </div>

      <div class="wow-range-calendar__month">
        <div class="wow-range-calendar__month-label">{{ monthLabel.format(rightMonth) }}</div>
        <div class="wow-range-calendar__daynames">
          <span v-for="day in dayLabels" :key="`right-${day}`" class="wow-range-calendar__dayname">{{ day }}</span>
        </div>
        <div class="wow-range-calendar__grid">
          <button
            v-for="(cell, index) in monthCells(rightMonth)"
            :key="`right-${index}`"
            type="button"
            class="wow-range-calendar__cell"
            :class="{
              'is-empty': !cell,
              'is-disabled': !!cell && isPast(cell),
              'is-selected': !!cell && (isRangeStart(cell) || isRangeEnd(cell)),
              'is-range': !!cell && inRange(cell),
              'is-start': !!cell && isRangeStart(cell),
              'is-end': !!cell && isRangeEnd(cell),
            }"
            :disabled="!cell || isPast(cell)"
            :aria-disabled="(!cell || isPast(cell)).toString()"
            @click="cell && selectDate(cell)"
          >
            <span v-if="cell">{{ cell.getDate() }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.wow-range-calendar{
  width:100%;
  background:#fff;
  border:1px solid rgba(15, 23, 42, .08);
  border-radius:18px;
  overflow:hidden;
}
.wow-range-calendar__toolbar{
  display:grid;
  grid-template-columns:minmax(0, 1fr) auto;
  align-items:center;
  column-gap:12px;
  padding:14px 16px;
  border-bottom:1px solid rgba(15, 23, 42, .08);
  background:linear-gradient(180deg,#fff,#fbfcfd);
}
.wow-range-calendar__toolbar-title{
  display:flex;
  align-items:center;
  gap:12px;
  flex:1 1 auto;
  min-width:0;
}
.wow-range-calendar__toolbar-title > i{
  font-size:22px;
  color:#0f172a;
}
.wow-range-calendar__eyebrow{
  color:#6b7280;
  font-size:11px;
  line-height:1;
  font-weight:700;
  margin-bottom:4px;
}
.wow-range-calendar__headline{
  color:#0f172a;
  font-size:15px;
  line-height:1;
  font-weight:700;
}
.wow-range-calendar__toolbar-actions{
  display:flex;
  align-items:center;
  gap:8px;
  justify-self:end;
}
.wow-range-calendar__nav{
  width:34px;
  height:34px;
  border-radius:999px;
  border:1px solid rgba(15, 23, 42, .12);
  background:#fff;
  color:#0f172a;
  display:inline-flex;
  align-items:center;
  justify-content:center;
}
.wow-range-calendar__nav:disabled{
  opacity:.4;
  cursor:not-allowed;
}
.wow-range-calendar__months{
  display:grid;
  grid-template-columns:repeat(2,minmax(0,1fr));
  gap:12px;
  padding:14px;
}
.wow-range-calendar__month{
  min-width:0;
}
.wow-range-calendar__month-label{
  font-size:13px;
  font-weight:700;
  color:#0f172a;
  margin-bottom:10px;
}
.wow-range-calendar__daynames,
.wow-range-calendar__grid{
  display:grid;
  grid-template-columns:repeat(7,minmax(0,1fr));
  gap:6px;
}
.wow-range-calendar__daynames{
  margin-bottom:6px;
}
.wow-range-calendar__dayname{
  font-size:11px;
  color:#6b7280;
  text-align:center;
  font-weight:700;
  line-height:1;
  padding-bottom:2px;
}
.wow-range-calendar__cell{
  height:38px;
  border-radius:11px;
  border:1px solid rgba(15, 23, 42, .10);
  background:#fff;
  color:#0f172a;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:13px;
  font-weight:600;
}
.wow-range-calendar__cell.is-empty{
  border-color:transparent;
  background:transparent;
}
.wow-range-calendar__cell.is-disabled{
  opacity:.35;
  cursor:not-allowed;
  background:#f8fafc;
}
.wow-range-calendar__cell.is-range{
  background:rgba(84,148,131,.12);
  border-color:rgba(84,148,131,.16);
}
.wow-range-calendar__cell.is-selected,
.wow-range-calendar__cell.is-start,
.wow-range-calendar__cell.is-end{
  background:#549483;
  border-color:#549483;
  color:#fff;
  box-shadow:0 8px 18px rgba(16, 24, 40, .12);
}

@media (max-width: 768px){
  .wow-range-calendar__toolbar{
    padding:12px 14px;
    column-gap:10px;
  }

  .wow-range-calendar__toolbar-title{
    gap:10px;
  }

  .wow-range-calendar__eyebrow{
    font-size:10px;
  }

  .wow-range-calendar__headline{
    font-size:14px;
  }

  .wow-range-calendar__months{
    grid-template-columns:1fr;
  }

  .wow-range-calendar__month:last-child{
    display:none;
  }
}
</style>

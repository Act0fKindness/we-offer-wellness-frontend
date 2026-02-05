<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;

class TestVariantInspector extends Command
{
    protected $signature = 'wow:test-variant-inspector {--limit= : Limit number of products} {--product= : Specific product id}';
    protected $description = 'Simulate Debug Variant Inspector matching across products and report mismatches';

    public function handle()
    {
        $limit = $this->option('limit') ? (int)$this->option('limit') : null;
        $only = $this->option('product') ? (int)$this->option('product') : null;

        $q = Product::query()
            ->with(['options.values','variants'])
            ->orderBy('id','asc');
        if ($only) $q->where('id', $only);
        if ($limit) $q->limit($limit);

        $total = 0; $fails = 0; $failed = [];
        $this->info("Testing variant resolution using Debug Variant Inspector logic...");
        foreach ($q->cursor() as $p) {
            $total++;
            [$optionsArr, $variantsArr] = $this->shape($p);
            $issues = $this->checkProduct($p, $optionsArr, $variantsArr);
            if (!empty($issues)) {
                $fails++;
                $url = $this->productUrl($p);
                $failed[] = [ 'id' => $p->id, 'title' => (string)$p->title, 'url' => $url, 'issues' => $issues ];
                $this->line("- FAIL: {$p->id} {$p->title} -> {$url}");
                foreach ($issues as $msg) { $this->line("    • $msg"); }
            }
        }
        $this->newLine();
        $this->info("Scanned $total products. Failing: $fails");
        if ($fails) {
            $this->newLine();
            $this->info("Problem URLs:");
            foreach ($failed as $f) {
                $this->line("- {$f['url']}");
            }
        }
        return 0;
    }

    private function shape($product)
    {
        // Build options array and a lookup of value id -> label
        $valueLookup = [];
        $optionsArr = $product->options->map(function($o) use (&$valueLookup){
            $vals = $o->values->pluck('value', 'id')->all();
            foreach ($vals as $id => $label) { $valueLookup[$id] = $label; }
            return [
                'name' => $o->name ?? $o->meta_name,
                'meta_name' => $o->meta_name,
                'values' => array_values($vals),
            ];
        })->values()->all();

        $variantsArr = $product->variants->map(function($v) use ($valueLookup){
            $optList = [];
            if (is_array($v->options) && count($v->options)) {
                // If associative like {option1:..., option2:...}, preserve option1.. order
                $ordered = [];
                $leftover = [];
                foreach ($v->options as $k => $val) {
                    if (preg_match('/^option\d+$/', (string)$k)) $ordered[$k] = $val; else $leftover[$k] = $val;
                }
                if (!empty($ordered)) {
                    ksort($ordered);
                    foreach ($ordered as $val) $optList[] = $val;
                    foreach ($leftover as $val) $optList[] = $val;
                } else {
                    $optList = array_values($v->options);
                }
            } elseif (!empty($v->option_ids)) {
                $ids = json_decode($v->option_ids, true) ?: [];
                foreach ($ids as $oid) {
                    if (isset($valueLookup[$oid])) { $optList[] = $valueLookup[$oid]; }
                }
            }
            return [
                'id' => $v->id,
                'options' => $optList,
                'price' => (float)$v->price,
                'compare' => is_array($v->metadata ?? null) && isset($v->metadata['compare_at_price']) ? (float)$v->metadata['compare_at_price'] : null,
                'available' => ($v->inventory_quantity ?? 0) > 0 || is_null($v->inventory_quantity),
            ];
        })->values()->all();

        return [$optionsArr, $variantsArr];
    }

    private function checkProduct($p, $optionsArr, $variants)
    {
        $issues = [];
        if (empty($variants)) { return [ 'no variants' ]; }

        // Determine indices
        $pi = $si = $li = null;
        foreach ($optionsArr as $i => $opt) {
            $nm = strtolower((string)($opt['name'] ?? $opt['meta_name'] ?? ''));
            if ($pi === null && str_contains($nm, 'person')) $pi = $i;
            if ($si === null && str_contains($nm, 'session')) $si = $i;
            if ($li === null && str_contains($nm, 'location')) $li = $i;
        }
        $peopleVals = ($pi !== null) ? ($optionsArr[$pi]['values'] ?? []) : [];
        $sessionVals = ($si !== null) ? ($optionsArr[$si]['values'] ?? []) : [];
        $locVals = ($li !== null) ? ($optionsArr[$li]['values'] ?? []) : [];
        if (empty($peopleVals)) $peopleVals = [null];
        if (empty($sessionVals)) $sessionVals = [null];
        if (empty($locVals)) $locVals = [null];

        // Helper closures matching Vue logic
        $norm = function($s){ return strtolower(preg_replace('~[^a-z0-9]+~','', (string)$s)); };
        $num = function($s){ $m = []; if (preg_match('~\d+~', (string)$s, $m)) return (int)$m[0]; return null; };
        $kvFor = function($v) use ($variants, $optionsArr, $si, $sessionVals, $norm, $num){
            $kv = [];
            $toks = array_map('strval', $v['options'] ?? []);
            foreach ($toks as $t){
                $lc = strtolower($t);
                if (str_contains($lc,'online')) $kv['format'] = 'Online';
                if (str_contains($lc,'in-person') || str_contains($lc,'in person') || str_contains($lc,'inperson')) $kv['format'] = 'In-person';
                $n = $num($t); if ($n !== null && !isset($kv['people'])) $kv['people'] = $n;
            }
            if (!isset($kv['location'])){
                if (in_array('online', array_map($norm,$toks), true)) $kv['location'] = 'Online';
                else {
                    // Try to infer from session option labels (not ideal, but aligns with Debug code using known locations)
                }
            }
            if (!isset($kv['format'])) $kv['format'] = (($kv['location'] ?? '') === 'Online') ? 'Online' : 'In-person';
            // Sessions mapping
            if ($si !== null) {
                $sVals = array_map(function($v){ return is_array($v) ? (string)($v['value'] ?? '') : (string)$v; }, ($optionsArr[$si]['values'] ?? []));
                $exact = null;
                foreach ($sVals as $lbl) { foreach ($toks as $t) { if ((string)$t === (string)$lbl) { $exact = $lbl; break 2; } } }
                if ($exact) $kv['sessions'] = $exact; else {
                    $wn = null; // no want here, try any numeric match
                    $tokNums = array_values(array_filter(array_map($num, $toks), fn($x)=>$x!==null));
                    foreach ($sVals as $lbl){ if ($num($lbl) !== null && in_array($num($lbl), $tokNums, true)) { $kv['sessions'] = $lbl; break; } }
                }
            }
            return $kv;
        };
        $matchesSession = function($v, $sLbl) use ($kvFor, $num){ if (!$sLbl) return true; $kv = $kvFor($v); $opts = $v['options'] ?? []; $wn = $num($sLbl); if (isset($kv['sessions'])) return ($wn !== null) ? ($num($kv['sessions']) === $wn) : ((string)$kv['sessions'] === (string)$sLbl); return ($wn !== null) ? (bool)array_filter($opts, fn($t)=>$num($t)===$wn) : in_array((string)$sLbl, array_map('strval',$opts), true); };
        $matchesPeople = function($v, $n) use ($kvFor, $num){ if ($n===null) return true; $kv = $kvFor($v); $opts = $v['options'] ?? []; if (isset($kv['people'])) return ((int)$kv['people'] === (int)$n); return (bool)array_filter($opts, fn($t)=>$num($t)===(int)$n); };
        $matchesLocation = function($v, $loc) use ($kvFor, $norm){ if (!$loc) return true; $w = $norm($loc); $kv=$kvFor($v); $have = isset($kv['location']) ? $norm($kv['location']) : ''; if ($have) return $have===$w || str_contains($have,$w) || str_contains($w,$have); $opts = $v['options'] ?? []; foreach ($opts as $t){ $tn = $norm($t); if ($tn && ($tn===$w || str_contains($tn,$w) || str_contains($w,$tn))) return true; } return false; };

        $sessionsOrdered = array_map(fn($v)=> is_array($v) ? (string)($v['value'] ?? '') : (string)$v, $sessionVals);
        $rankBySessions = function($v) use ($kvFor, $sessionsOrdered, $num){ $kv = $kvFor($v); $lbl = (string)($kv['sessions'] ?? ''); $idx = array_search($lbl, $sessionsOrdered, true); if ($idx !== false) return (int)$idx; $kn = $num($lbl); if ($kn !== null) { foreach ($sessionsOrdered as $i => $sv) { if ($num($sv) === $kn) return $i; } } return 999; };

        // Iterate combinations
        $seenDiffByLoc = [];
        foreach ($locVals as $loc) {
            foreach ($sessionVals as $s) {
                foreach ($peopleVals as $pval) {
                    $sel = array_fill(0, count($optionsArr), '');
                    if ($pi !== null && $pval !== null) $sel[$pi] = (string)$pval;
                    if ($si !== null && $s !== null) $sel[$si] = (string)$s;
                    if ($li !== null && $loc !== null) $sel[$li] = (string)$loc;

                    // Location-first pool
                    $pool = $loc ? array_values(array_filter($variants, fn($v) => $matchesLocation($v, $loc))) : $variants;
                    if (!empty($pool)) {
                        if ($s) { $hit = array_values(array_filter($pool, fn($v)=>$matchesSession($v, $s))); if (!empty($hit)) $pool = $hit; }
                        if ($pval !== null) { $wantN = $num($pval); if ($wantN !== null) { $hit = array_values(array_filter($pool, fn($v)=>$matchesPeople($v, $wantN))); if (!empty($hit)) $pool = $hit; } }
                    }
                    $chosen = null;
                    if (count($pool) === 1) $chosen = $pool[0];
                    elseif (!empty($pool)) {
                        // Rank by sessions order
                        usort($pool, function($a,$b) use ($rankBySessions){ return $rankBySessions($a) <=> $rankBySessions($b); });
                        $chosen = $pool[0];
                    } else {
                        // fallback to global filtering exact
                        $cands = array_values(array_filter($variants, function($v) use ($matchesPeople,$matchesSession,$matchesLocation,$loc,$s,$pval){
                            return $matchesPeople($v, $pval===null?null:(int)preg_replace('~[^0-9]+~','',(string)$pval)) && $matchesSession($v, $s) && $matchesLocation($v, $loc);
                        }));
                        if (count($cands)===1) $chosen=$cands[0];
                        elseif (!empty($cands)) $chosen=$cands[0];
                    }

                    if (!$chosen) {
                        $issues[] = "No variant for loc=[".($loc??'')."], sessions=[".($s??'')."], people=[".($pval??'')."]";
                        // No need to continue deeply; but keep scanning to collect more
                        continue;
                    }

                    // Track difference by location for same sessions
                    if ($loc && $s) {
                        $key = (string)$s;
                        $seenDiffByLoc[$key] = $seenDiffByLoc[$key] ?? [];
                        $seenDiffByLoc[$key][$loc] = (string)$chosen['id'];
                    }
                }
            }
        }

        foreach ($seenDiffByLoc as $sLbl => $map) {
            if (count($map) > 1) {
                $ids = array_values($map);
                if (count(array_unique($ids)) === 1) {
                    $issues[] = "Location changes did not change variant for sessions='$sLbl'";
                }
            }
        }

        return array_values(array_unique($issues));
    }

    private function productUrl($p): string
    {
        $seg = $this->typeSegment($p);
        $slug = $this->slugify($p->title ?? (string)$p->id);
        return url('/'.$seg.'/'.$p->id.'-'.$slug);
    }

    private function slugify(string $name): string
    {
        $s = strtolower(trim($name));
        $s = preg_replace('~[^a-z0-9]+~', '-', $s ?? '') ?? '';
        return trim($s, '-');
    }

    private function typeSegment($p): string
    {
        $t = strtolower((string) $p->product_type);
        $tags = strtolower((string) $p->tags_list);
        if (str_contains($t, 'workshop')) return 'workshops';
        if (str_contains($t, 'event')) return 'events';
        if (str_contains($t, 'class')) return 'classes';
        if (str_contains($t, 'retreat')) return 'retreats';
        if (str_contains($t, 'gift') || str_contains($tags, 'gift')) return 'gifts';
        return 'therapies';
    }
}


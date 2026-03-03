<?php

namespace App\Support;

class ContentFormatter
{
    /**
     * Format admin-entered rich text for offering sections.
     * - Normalizes line breaks
     * - Collapses excessive blank lines
     * - Replaces common emojis with Font Awesome icons
     * - Converts plain text to safe HTML paragraphs/line breaks
     * - Preserves simple HTML if already present (cleans multiple <br>)
     */
    public static function format(?string $input): string
    {
        $s = trim((string) $input);
        if ($s === '') return '';

        // Normalize line endings and stray spaces
        $s = str_replace(["\r\n", "\r"], "\n", $s);
        $s = preg_replace('/[\t ]+\n/', "\n", $s);

        // Emoji → Font Awesome (simple, conservative mapping)
        $emojiMap = [
            '🌿' => '<i class="fa-solid fa-seedling" aria-hidden="true"></i>',
            '✅' => '<i class="fa-solid fa-check" aria-hidden="true"></i>',
            '✨' => '<i class="fa-solid fa-star" aria-hidden="true"></i>',
            '⚡' => '<i class="fa-solid fa-bolt" aria-hidden="true"></i>',
            '💚' => '<i class="fa-regular fa-heart" aria-hidden="true"></i>',
            '❤️' => '<i class="fa-solid fa-heart" aria-hidden="true"></i>',
        ];
        $s = strtr($s, $emojiMap);

        // Collapse 3+ blank lines → 2 (for paragraph separation)
        $s = preg_replace("/\n{3,}/", "\n\n", $s);

        // If content already includes HTML tags, trust it but clean common issues
        if (preg_match('/<\w+[^>]*>/', $s)) {
            // Reduce excessive <br>
            $s = preg_replace('/(<br\s*\/?>\s*){2,}/i', '<br/>', $s);
            // Convert &nbsp; runs to single spaces
            $s = preg_replace('/(&nbsp;\s*)+/i', ' ', $s);
            return self::stripEmojiImages($s);
        }

        // Plain text → paragraphs; escape HTML first, keep our FA icons intact
        // Protect FA tags from escaping by temporary tokens
        $token = "__FA_TOKEN__" . uniqid('', true) . "__";
        $fa = [];
        // Extract FA snippets to tokens
        $s = preg_replace_callback('/<i class=\\"fa-[^>]+><\\/i>/', function($m) use (&$fa, $token){
            $k = $token . count($fa) . '__';
            $fa[$k] = $m[0];
            return $k;
        }, $s);

        $s = htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

        // Restore FA snippets
        $s = strtr($s, $fa);

        // Split paragraphs on double newlines
        $paras = preg_split("/\n\n+/", trim($s));
        $out = '';
        foreach ($paras as $p) {
            $p = preg_replace('/\n/', '<br/>', trim($p));
            if ($p !== '') $out .= '<p>'.$p.'</p>';
        }
        $final = $out ?: $s;
        return self::stripEmojiImages($final);
    }

    private static function stripEmojiImages(string $html): string
    {
        $cleaned = preg_replace_callback('/<img\b[^>]*>/i', function ($match) {
            $tag = $match[0] ?? '';
            if ($tag === '') {
                return $tag;
            }
            $lc = strtolower($tag);
            $targets = ['fonts.gstatic.com', 'gstatic.com/emoji', 'data-emoji', 'class="an1', 'class=\'an1', 'emoji-img'];
            foreach ($targets as $needle) {
                if (str_contains($lc, $needle)) {
                    return '';
                }
            }
            if (str_contains($lc, '&lt;i class') || str_contains($lc, '<i class')) {
                return '';
            }
            return $tag;
        }, $html) ?? $html;

        // Remove any stray attribute fragments left behind by stripped emoji tags
        $cleaned = preg_replace('/"\s*loading="lazy"[^>]*data-emoji="[^"]*"[^>]*>\s*/i', '', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/&quot;\s*loading="lazy"[^>]*data-emoji="[^"]*"[^>]*&gt;\s*/i', '', $cleaned) ?? $cleaned;

        return $cleaned;
    }
}

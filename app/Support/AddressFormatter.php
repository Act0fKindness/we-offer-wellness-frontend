<?php

namespace App\Support;

class AddressFormatter
{
    /**
     * Return a compact human-friendly location from a raw address string.
     * Examples:
     *  - "Flower of Life..., Mile End Road, South Shields, England, UK" → "South Shields"
     *  - "Sidmouth, Devon, United Kingdom" → "Sidmouth"
     */
    public static function short(string $address): string
    {
        $raw = trim($address);
        if ($raw === '') return '';

        $countryRx  = '/\b(United Kingdom|UK|England|Scotland|Wales|Northern Ireland)\b/i';
        $postcodeRx = '/\b[A-Z]{1,2}\d{1,2}[A-Z]?\s*\d[A-Z]{2}\b/i';
        $streetRx   = '/\b(road|rd|street|st|avenue|ave|lane|ln|drive|dr|way|close|cl|place|pl|boulevard|blvd|court|ct|crescent|cresc|terrace|terr|grove|grv)\b/i';

        $tokens = array_map('trim', explode(',', $raw));
        // Remove empty and country tokens
        $tokens = array_values(array_filter($tokens, fn($t) => $t !== '' && !preg_match($countryRx, $t)));
        // Remove token if looks like a UK postcode
        $tokens = array_values(array_filter($tokens, fn($t) => !preg_match($postcodeRx, $t)));
        // Prefer the last non-street token as city
        $nonStreet = array_values(array_filter($tokens, fn($t) => !preg_match($streetRx, $t)));
        if (count($nonStreet) >= 1) {
            $city = $nonStreet[count($nonStreet) - 1];
            // If the first non-street token differs from city, we can keep both ("Place, City")
            $place = $nonStreet[0];
            return strcasecmp($place, $city) === 0 ? $city : ($place . ', ' . $city);
        }
        // Fallback to last token or original
        if (!empty($tokens)) return $tokens[count($tokens) - 1];
        return $raw;
    }

    /**
     * Return "City, UK" style string from raw address. Uses short() to derive the city.
     */
    public static function cityCountry(string $address, string $countryLabel = 'UK'): string
    {
        $short = self::short($address);
        if ($short === '') return '';
        // Avoid duplicating country label
        if (stripos($short, $countryLabel) !== false) return $short;
        return $short . ', ' . $countryLabel;
    }
}


<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProductSearchFilters
{
    private const PERSON_META_NAMES = ['persons', 'person', 'people'];
    private const PERSON_LABELS = ['person', 'persons', 'person(s)', 'people', 'guests'];
    private const LOCATION_META_NAMES = ['locations', 'location'];
    private const LOCATION_LABELS = ['location', 'locations', 'location(s)'];
    private const VENDOR_LOCATION_COLUMNS = [
        'vendor_locations.label',
        'vendor_locations.line1',
        'vendor_locations.line2',
        'vendor_locations.city',
        'vendor_locations.county',
        'vendor_locations.postcode',
        'vendor_locations.formatted_address',
        'vendor_locations.country',
    ];

    public static function applyWhereFilter(Builder $query, ?string $rawValue): void
    {
        $places = self::parsePlaces($rawValue);
        if ($places->isEmpty()) {
            return;
        }

        $query->where(function (Builder $outer) use ($places) {
            foreach ($places as $place) {
                $outer->orWhere(function (Builder $inner) use ($place) {
                    if (self::isOnlineTerm($place)) {
                        self::applyOnlineLocationConstraint($inner);
                    } else {
                        self::applyPhysicalLocationConstraint($inner, $place);
                    }
                });
            }
        });
    }

    public static function applyWhoFilter(Builder $query, ?int $adults, ?string $groupType): void
    {
        $mode = self::resolveWhoMode($adults, $groupType);
        if (!$mode) {
            return;
        }

        $query->whereHas('options', function (Builder $options) use ($mode) {
            self::scopePersonOptions($options);
            $options->whereHas('values', function (Builder $values) use ($mode) {
                self::applyPersonsValueConstraint($values, $mode);
            });
        });
    }

    private static function parsePlaces(?string $raw): Collection
    {
        if (!$raw) {
            return collect();
        }

        return collect(preg_split('/[|,]/', $raw))
            ->map(fn ($part) => trim((string) $part))
            ->filter()
            ->values();
    }

    private static function isOnlineTerm(string $value): bool
    {
        return Str::lower(trim($value)) === 'online';
    }

    private static function applyOnlineLocationConstraint(Builder $query): void
    {
        $query->where(function (Builder $online) {
            $online->whereHas('options', function (Builder $options) {
                self::scopeLocationOptions($options);
                $options->whereHas('values', function (Builder $values) {
                    $values->where(function (Builder $match) {
                        $match->whereRaw('LOWER(product_option_values.value) = ?', ['online'])
                              ->orWhereRaw('LOWER(product_option_values.value) LIKE ?', ['%online%']);
                    });
                });
            })->orWhereHas('variants', function (Builder $variants) {
                $variants->whereRaw('LOWER(COALESCE(product_variants.options, \'\')) LIKE ?', ['%online%']);
            });
        });
    }

    private static function applyPhysicalLocationConstraint(Builder $query, string $term): void
    {
        $pattern = self::likePattern($term);

        $query->where(function (Builder $match) use ($pattern) {
            $match->whereHas('options', function (Builder $options) use ($pattern) {
                self::scopeLocationOptions($options);
                $options->whereHas('values', function (Builder $values) use ($pattern) {
                    $values->whereRaw('LOWER(product_option_values.value) LIKE ?', [$pattern]);
                });
            })->orWhereHas('vendor.locations', function (Builder $locations) use ($pattern) {
                $locations->where(function (Builder $loc) use ($pattern) {
                    $first = true;
                    foreach (self::VENDOR_LOCATION_COLUMNS as $column) {
                        $condition = "LOWER(COALESCE({$column}, '')) LIKE ?";
                        if ($first) {
                            $loc->whereRaw($condition, [$pattern]);
                            $first = false;
                        } else {
                            $loc->orWhereRaw($condition, [$pattern]);
                        }
                    }
                });
            });
        });
    }

    private static function resolveWhoMode(?int $rawAdults, ?string $groupType): ?string
    {
        $adults = $rawAdults !== null && $rawAdults > 0 ? $rawAdults : null;
        $group = $groupType ? Str::lower(trim($groupType)) : null;

        if ($adults === null && !$group) {
            return null;
        }

        if ($group === 'group') {
            return 'group';
        }

        if ($group === 'couple') {
            return 'couple';
        }

        if ($group === 'solo') {
            return 'solo';
        }

        if ($adults !== null) {
            if ($adults >= 3) {
                return 'group';
            }
            if ($adults === 2) {
                return 'couple';
            }
            if ($adults === 1) {
                return 'solo';
            }
        }

        return null;
    }

    private static function scopePersonOptions(Builder $query): void
    {
        $query->where(function (Builder $opt) {
            $opt->where(function (Builder $meta) {
                self::addLowercaseEqualsConditions($meta, 'product_options.meta_name', self::PERSON_META_NAMES);
            })->orWhere(function (Builder $names) {
                self::addLowercaseEqualsConditions($names, 'product_options.name', self::PERSON_LABELS);
            });
        });
    }

    private static function scopeLocationOptions(Builder $query): void
    {
        $query->where(function (Builder $opt) {
            $opt->where(function (Builder $meta) {
                self::addLowercaseEqualsConditions($meta, 'product_options.meta_name', self::LOCATION_META_NAMES);
            })->orWhere(function (Builder $names) {
                self::addLowercaseEqualsConditions($names, 'product_options.name', self::LOCATION_LABELS);
            });
        });
    }

    private static function addLowercaseEqualsConditions(Builder $query, string $column, array $values): void
    {
        $normalized = array_values(array_unique(array_map(
            fn ($value) => Str::lower(trim((string) $value)),
            array_filter($values, fn ($value) => $value !== null && $value !== '')
        )));

        if (empty($normalized)) {
            $query->whereRaw('1 = 0');
            return;
        }

        $first = true;
        foreach ($normalized as $value) {
            $condition = "LOWER(COALESCE({$column}, '')) = ?";
            if ($first) {
                $query->whereRaw($condition, [$value]);
                $first = false;
            } else {
                $query->orWhereRaw($condition, [$value]);
            }
        }
    }

    private static function applyPersonsValueConstraint(Builder $values, string $mode): void
    {
        $column = 'product_option_values.value';

        $values->where(function (Builder $valueQuery) use ($mode, $column) {
            if ($mode === 'solo') {
                $valueQuery->whereRaw("{$column} REGEXP ?", ['(^|[^0-9])1([^0-9]|$)'])
                           ->orWhereRaw("LOWER({$column}) LIKE ?", ['%solo%'])
                           ->orWhereRaw("LOWER({$column}) LIKE ?", ['%single%']);
            } elseif ($mode === 'couple') {
                $valueQuery->whereRaw("{$column} REGEXP ?", ['(^|[^0-9])2([^0-9]|$)'])
                           ->orWhereRaw("LOWER({$column}) LIKE ?", ['%couple%'])
                           ->orWhereRaw("LOWER({$column}) LIKE ?", ['%pair%'])
                           ->orWhereRaw("LOWER({$column}) LIKE ?", ['%duo%']);
            } else {
                $pattern = '(^|[^0-9])([3-9]|[1-9][0-9]{1,})([^0-9]|$)';
                $valueQuery->whereRaw("{$column} REGEXP ?", [$pattern])
                           ->orWhereRaw("LOWER({$column}) LIKE ?", ['%group%'])
                           ->orWhereRaw("LOWER({$column}) LIKE ?", ['%family%'])
                           ->orWhereRaw("LOWER({$column}) LIKE ?", ['%team%']);
            }
        });
    }

    private static function likePattern(string $value): string
    {
        $normalized = Str::of($value)->lower()->toString();
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $normalized);

        return '%' . $escaped . '%';
    }
}

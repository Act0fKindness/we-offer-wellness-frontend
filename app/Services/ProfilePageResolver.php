<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProfilePageResolver
{
    public function resolve(string $slug, string $profileType = 'practitioner'): ?User
    {
        $slug = trim(rawurldecode($slug));
        if ($slug === '') {
            return null;
        }

        [$baseSlug, $hashSuffix] = $this->splitSlugHash($slug);
        if ($baseSlug === '') {
            return null;
        }

        $exactQuery = $this->applySlugFilters(
            $this->buildBaseQuery($profileType),
            $baseSlug
        );

        $candidates = $exactQuery->get();
        $match = $this->matchCandidate($candidates, $baseSlug, $hashSuffix);
        if ($match !== null) {
            return $match;
        }

        $fallbackCandidates = $this->buildBaseQuery($profileType)->get();

        return $this->matchCandidate($fallbackCandidates, $baseSlug, $hashSuffix);
    }

    public function buildSeo(User $user, string $profileType, string $canonical): array
    {
        $displayName = trim((string) ($user->full_name ?: $user->name ?: $user->vendorDetail?->vendor_name ?: ''));
        if ($displayName === '') {
            $displayName = $profileType === 'team' ? 'Team member' : 'Practitioner';
        }

        $rawBio = trim(strip_tags((string) ($user->bio?->bio ?? '')));
        $description = $rawBio !== ''
            ? Str::limit($rawBio, 155)
            : ($profileType === 'team'
                ? 'Meet the We Offer Wellness team member profile.'
                : 'Browse the practitioner profile, availability and offerings.');

        return [
            'title' => sprintf(
                '%s | %s | We Offer Wellness®',
                $displayName,
                $profileType === 'team' ? 'Team Profile' : 'Practitioner Profile'
            ),
            'description' => $description,
            'canonical' => $canonical,
            'robots' => 'index,follow',
            'og_image' => $user->cover_image_url
                ?: $user->profile_picture_url
                ?: asset('images/default-social-preview.jpg'),
            'og_image_alt' => $displayName . ' profile image',
            'site_name' => 'We Offer Wellness®',
            'twitter_card' => 'summary_large_image',
        ];
    }

    private function buildBaseQuery(string $profileType): Builder
    {
        $query = User::query()->with([
            'roles',
            'settings',
            'bio',
            'profile',
            'vendorDetail.locations',
            'vendorDetail.insurances.documents',
            'vendorDetail.customerReviews.user',
            'vendorDetail.customerReviews.product',
            'vendorDetail.tiers',
            'vendorDetail.products.media',
            'vendorDetail.products.category',
            'vendorDetail.products.status',
            'vendorDetail.offerings.media',
            'vendorDetail.offerings.coverMedia',
            'vendorDetail.offerings.category',
            'vendorDetail.offerings.type',
        ]);

        if ($profileType === 'team') {
            $query->whereHas('roles', function (Builder $roleQuery): void {
                $roleQuery->whereRaw('LOWER(name) = ?', ['admin']);
            });

            return $query;
        }

        $query->where(function (Builder $roleQuery): void {
            $roleQuery->where('is_vendor', true)
                ->orWhereHas('roles', function (Builder $roles): void {
                    $roles->whereRaw('LOWER(name) = ?', ['provider']);
                });
        });

        return $query;
    }

    private function applySlugFilters(Builder $query, string $slug): Builder
    {
        $query->where(function (Builder $builder) use ($slug): void {
            $builder->whereRaw(
                "LOWER(REPLACE(REPLACE(REPLACE(COALESCE(name, ''), '.', ''), ',', ''), ' ', '-')) = ?",
                [$slug]
            )->orWhereRaw(
                "LOWER(REPLACE(REPLACE(REPLACE(CONCAT_WS(' ', COALESCE(first_name, ''), COALESCE(last_name, '')), '.', ''), ',', ''), ' ', '-')) = ?",
                [$slug]
            )->orWhereHas('vendorDetail', function (Builder $vendorQuery) use ($slug): void {
                $vendorQuery->whereRaw(
                    "LOWER(REPLACE(REPLACE(REPLACE(COALESCE(vendor_name, ''), '.', ''), ',', ''), ' ', '-')) = ?",
                    [$slug]
                );
            });
        });

        return $query;
    }

    private function matchCandidate($candidates, string $baseSlug, ?string $hashSuffix): ?User
    {
        foreach ($candidates as $candidate) {
            if (! $candidate instanceof User) {
                continue;
            }

            if (! in_array($baseSlug, $this->candidateSlugs($candidate), true)) {
                continue;
            }

            if ($hashSuffix !== null && $this->profileHash($candidate) !== $hashSuffix) {
                continue;
            }

            return $candidate;
        }

        return null;
    }

    private function candidateSlugs(User $user): array
    {
        $values = [];

        foreach ([
            trim((string) ($user->full_name ?: '')),
            trim((string) ($user->name ?: '')),
            trim((string) ($user->vendorDetail?->vendor_name ?: '')),
        ] as $value) {
            if ($value === '') {
                continue;
            }

            $values[] = Str::slug($value);
        }

        return array_values(array_unique(array_filter($values)));
    }

    private function profileHash(User $user): string
    {
        return substr(hash('sha256', 'user:' . $user->getKey()), 0, 6);
    }

    private function splitSlugHash(string $slug): array
    {
        if (preg_match('/^(.*)-([a-f0-9]{6})$/i', $slug, $matches)) {
            return [trim((string) $matches[1], '-'), strtolower((string) $matches[2])];
        }

        return [trim($slug, '-'), null];
    }
}

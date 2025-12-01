<?php

namespace App\Helper;

class UrlHelper
{
    /**
     * Aggressively normalize url:
     * - remove scheme
     * - remove user/password/port
     * - remove utm_ params
     * - sort params
     * - remove fragment
     */
    public static function normalize(string $url): string
    {
        $parts = parse_url($url);

        $queryParams = [];
        if (isset($parts['query'])) {
            parse_str($parts['query'], $queryParams);
            $queryParams = array_filter(
                $queryParams,
                fn ($v, $k) => !str_starts_with((string) $k, 'utm_'),
                \ARRAY_FILTER_USE_BOTH
            );
            ksort($queryParams);
        }

        $newQuery = http_build_query($queryParams);

        $normalizedUrl = ($parts['host'] ?? '') . ($parts['path'] ?? '');

        if ('' !== $newQuery) {
            $normalizedUrl .= '?' . $newQuery;
        }

        return $normalizedUrl;
    }
}

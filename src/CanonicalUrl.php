<?php

namespace ThreeOhEight\Seo;

/**
 * Removes query parameters matching fnmatch() patterns (eg "utm_*") from a URL,
 * keeping every other parameter.
 */
final class CanonicalUrl
{
    /**
     * @param  list<string>  $patterns
     */
    public static function without(string $url, array $patterns): string
    {
        $query = parse_url($url, PHP_URL_QUERY);
        $base = strtok($url, '?');

        if (! is_string($query) || $query === '' || $base === false) {
            return $url;
        }

        parse_str($query, $params);

        $kept = array_filter(
            $params,
            static fn (int|string $key): bool => ! array_any(
                $patterns,
                static fn (string $pattern): bool => fnmatch($pattern, (string) $key),
            ),
            ARRAY_FILTER_USE_KEY,
        );

        return $kept === [] ? $base : $base.'?'.http_build_query($kept);
    }
}

<?php

namespace ThreeOhEight\Seo\Routing;

final class RouteSeo
{
    public const KEY = 'seo';

    /** Scalar fields accepted from the route action array. */
    private const FIELDS = ['title', 'description', 'robots', 'canonical', 'og_type'];

    /**
     * Normalize a raw 'seo' route action value into field => value pairs.
     *
     * Nested route groups that both define a field go through Laravel's
     * array_merge_recursive, which turns the scalar into a list with the
     * innermost group appended last — so for lists the last element wins.
     *
     * @param  array<string, mixed>  $raw
     * @return array<string, string>
     */
    public static function normalize(array $raw): array
    {
        $values = [];

        foreach (self::FIELDS as $field) {
            $value = $raw[$field] ?? null;

            if (is_array($value)) {
                $value = end($value) ?: null;
            }

            if (is_string($value) && $value !== '') {
                $values[$field] = $value;
            }
        }

        $noindex = $raw['noindex'] ?? null;

        if (is_array($noindex)) {
            $noindex = end($noindex);
        }

        if ($noindex === true && ! isset($values['robots'])) {
            $values['robots'] = 'noindex, nofollow';
        }

        return $values;
    }
}

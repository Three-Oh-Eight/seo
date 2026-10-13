<?php

use ThreeOhEight\Seo\Tests\TestCase;

uses(TestCase::class)->in('Feature');

/**
 * Decode every JSON-LD script in the rendered HTML.
 *
 * @return list<array<string, mixed>>
 */
function jsonLdBlocks(string $html): array
{
    preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);

    return array_map(
        static fn (string $json): array => json_decode($json, true, flags: JSON_THROW_ON_ERROR),
        $matches[1],
    );
}

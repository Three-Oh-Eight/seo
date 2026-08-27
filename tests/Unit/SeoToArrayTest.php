<?php

it('returns the full resolved structure', function () {
    $seo = $this->makeSeo(['og_image' => '/fallback.png', 'twitter_site' => '@testsite']);

    $seo->title('Dashboard')
        ->description('Overview')
        ->canonical('https://example.com/dash')
        ->robots('noindex')
        ->prev('https://example.com?page=1')
        ->next('https://example.com?page=3')
        ->meta('author', 'Christoph')
        ->alternate('nl', 'https://example.com/nl');

    expect($seo->toArray())->toBe([
        'title' => 'Dashboard - TestSite',
        'description' => 'Overview',
        'canonical' => 'https://example.com/dash',
        'robots' => 'noindex',
        'prev' => 'https://example.com?page=1',
        'next' => 'https://example.com?page=3',
        'meta' => ['author' => 'Christoph'],
        'alternates' => ['nl' => 'https://example.com/nl'],
        'og' => [
            'type' => 'website',
            'site_name' => 'TestSite',
            'title' => 'Dashboard - TestSite',
            'description' => 'Overview',
            'url' => 'https://example.com/dash',
            'locale' => null,
            'alternate_locales' => [],
            'image' => '/fallback.png',
            'image_width' => null,
            'image_height' => null,
            'image_type' => null,
            'image_alt' => null,
        ],
        'twitter' => [
            'card' => 'summary_large_image',
            'title' => 'Dashboard - TestSite',
            'description' => 'Overview',
            'image' => null,
            'site' => '@testsite',
        ],
    ]);
});

it('reflects cascade fallbacks with no page data', function () {
    $seo = $this->makeSeo(['description' => 'Default desc']);

    $array = $seo->toArray();

    expect($array['title'])->toBe('TestSite')
        ->and($array['description'])->toBe('Default desc')
        ->and($array['og']['title'])->toBe('TestSite')
        ->and($array['twitter']['description'])->toBe('Default desc');
});

it('reflects platform-specific overrides', function () {
    $seo = $this->makeSeo();
    $seo->title('Page')->og()->title('OG Page');
    $seo->og()->type('article');

    $array = $seo->toArray();

    expect($array['og']['title'])->toBe('OG Page')
        ->and($array['og']['type'])->toBe('article')
        ->and($array['twitter']['title'])->toBe('Page - TestSite');
});

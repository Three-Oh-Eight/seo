<?php

use ThreeOhEight\Seo\SeoDefaults;

it('creates defaults from full config array', function () {
    $defaults = SeoDefaults::fromConfig([
        'site_name' => 'MySite',
        'separator' => ' | ',
        'title' => 'Default Title',
        'description' => 'Default description',
        'auto_canonical' => false,
        'robots' => 'index, follow',
        'og_type' => 'article',
        'og_image' => '/og.png',
        'twitter_card' => 'summary',
        'twitter_image' => '/tw.png',
        'twitter_site' => '@mysite',
    ]);

    expect($defaults->siteName)->toBe('MySite')
        ->and($defaults->separator)->toBe(' | ')
        ->and($defaults->title)->toBe('Default Title')
        ->and($defaults->description)->toBe('Default description')
        ->and($defaults->autoCanonical)->toBeFalse()
        ->and($defaults->robots)->toBe('index, follow')
        ->and($defaults->ogType)->toBe('article')
        ->and($defaults->ogImage)->toBe('/og.png')
        ->and($defaults->twitterCard)->toBe('summary')
        ->and($defaults->twitterImage)->toBe('/tw.png')
        ->and($defaults->twitterSite)->toBe('@mysite');
});

it('applies sensible defaults for missing keys', function () {
    $defaults = SeoDefaults::fromConfig([]);

    expect($defaults->separator)->toBe(' - ')
        ->and($defaults->title)->toBeNull()
        ->and($defaults->description)->toBeNull()
        ->and($defaults->autoCanonical)->toBeTrue()
        ->and($defaults->robots)->toBeNull()
        ->and($defaults->ogType)->toBe('website')
        ->and($defaults->ogImage)->toBeNull()
        ->and($defaults->twitterCard)->toBe('summary_large_image')
        ->and($defaults->twitterImage)->toBeNull()
        ->and($defaults->twitterSite)->toBeNull();
});

it('falls back to app.name for site_name', function () {
    config(['app.name' => 'MyApp']);

    $defaults = SeoDefaults::fromConfig([]);

    expect($defaults->siteName)->toBe('MyApp');
});

it('uses explicit site_name over app.name', function () {
    config(['app.name' => 'MyApp']);

    $defaults = SeoDefaults::fromConfig(['site_name' => 'ExplicitName']);

    expect($defaults->siteName)->toBe('ExplicitName');
});

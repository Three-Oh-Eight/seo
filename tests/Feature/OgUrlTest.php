<?php

use Laravel\Head\Facades\Head;
use Laravel\Head\HeadManager;

it('renders og:url from the automatic canonical', function () {
    $array = null;
    $this->headRoute('/plain', function () use (&$array) {
        $array = Head::toArray();
    });

    $this->get('/plain?utm_source=news')
        ->assertSee('<meta property="og:url" content="https://localhost/plain">', false);

    expect($array['ogUrl'])->toBe('https://localhost/plain');
});

it('follows an explicit canonical', function () {
    $this->headRoute('/page', fn () => Head::canonical('https://example.com/canonical'));

    $this->get('/page')
        ->assertSee('<meta property="og:url" content="https://example.com/canonical">', false);
});

it('follows a stripped canonical', function () {
    $this->headRoute('/page', fn () => Head::canonicalWithout(['utm_*']));

    $this->get('/page?id=42&utm_source=news')
        ->assertSee('<meta property="og:url" content="https://localhost/page?id=42">', false);
});

it('lets a runtime og url override the default', function () {
    $this->headRoute('/page', fn () => Head::og(url: 'https://example.com/share'));

    $html = $this->get('/page')->getContent();

    expect(substr_count($html, 'property="og:url"'))->toBe(1)
        ->and($html)->toContain('<meta property="og:url" content="https://example.com/share">');
});

it('lets a route og url override the default', function () {
    $this->headRoute('/page')->withHead(og: ['url' => 'https://example.com/route-share']);

    $html = $this->get('/page')->getContent();

    expect(substr_count($html, 'property="og:url"'))->toBe(1)
        ->and($html)->toContain('<meta property="og:url" content="https://example.com/route-share">');
});

it('can be turned off per route', function () {
    $this->headRoute('/page')->withHead(extensions: ['ogUrl' => false]);

    $this->get('/page')
        ->assertSee('rel="canonical"', false)
        ->assertDontSee('og:url', false);
});

it('can be turned off in config', function () {
    config()->set('seo.og_url', false);
    $this->headRoute('/page');

    $this->get('/page')->assertDontSee('og:url', false);
});

it('renders nothing without a canonical url', function () {
    $this->withSeoConfig(['auto_canonical' => false]);
    $this->headRoute('/page');

    $this->get('/page')->assertDontSee('og:url', false);
});

it('renders exactly one og url after head is re-resolved', function () {
    $this->headRoute('/page');

    expect(substr_count($this->get('/page')->assertOk()->getContent(), 'property="og:url"'))->toBe(1);

    // Octane and the test harness rebuild the manager (and its tag registry); the hook must not stack up.
    $this->app->forgetInstance(HeadManager::class);
    Head::clearResolvedInstance();
    $this->app->forgetInstance(HeadManager::class);
    Head::clearResolvedInstance();

    $html = $this->get('/page')->assertOk()->getContent();

    expect(substr_count($html, 'property="og:url"'))->toBe(1)
        ->and($html)->toContain('<meta property="og:url" content="https://localhost/page">');
});

it('renders nothing on pages without open graph data', function () {
    $this->withSeoConfig(['site_name' => null, 'description' => null, 'og_type' => null, 'og_image' => null]);
    $this->headRoute('/page');

    $this->get('/page')
        ->assertSee('rel="canonical"', false)
        ->assertDontSee('og:', false);
});

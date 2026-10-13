<?php

use Laravel\Head\Facades\Head;
use Laravel\Head\HeadManager;

it('drops the whole query string on the automatic canonical', function () {
    $this->withSeoConfig(['canonical_strip' => ['utm_*']]);
    $this->headRoute('/products');

    $this->get('/products?page=2&utm_source=google')
        ->assertSee('<link rel="canonical" href="https://localhost/products">', false);
});

it('keeps the query string minus the config patterns', function () {
    $this->withSeoConfig(['canonical_strip' => ['utm_*', 'fbclid']]);
    $this->headRoute('/products', fn () => Head::canonicalWithout());

    $html = $this->get('/products?category=tools&page=2&utm_source=news&utm_medium=mail&fbclid=abc')->getContent();

    expect($html)->toContain('<link rel="canonical" href="https://localhost/products?category=tools&amp;page=2">')
        ->and($html)->not->toContain('utm_')
        ->and($html)->not->toContain('fbclid');
});

it('combines config patterns with per-page params', function () {
    $this->withSeoConfig(['canonical_strip' => ['utm_*']]);
    $this->headRoute('/page', fn () => Head::canonicalWithout(['ref', 'sort']));

    $html = $this->get('/page?utm_source=google&ref=partner&sort=price&id=42')->getContent();

    expect($html)->toContain('href="https://localhost/page?id=42"');
});

it('returns a clean url when every param is stripped', function () {
    $this->headRoute('/page', fn () => Head::canonicalWithout(['utm_*', 'fbclid']));

    $this->get('/page?utm_source=google&fbclid=abc')
        ->assertSee('<link rel="canonical" href="https://localhost/page">', false);
});

it('overrides a route canonical, and a later explicit canonical wins', function () {
    $this->headRoute('/page', function () {
        expect(Head::canonicalWithout(['sort']))->toBeInstanceOf(HeadManager::class);

        Head::canonical('https://example.com/page?utm_source=keep');
    })->withHead(canonical: 'https://example.com/route');

    $this->get('/page?sort=asc')
        ->assertSee('<link rel="canonical" href="https://example.com/page?utm_source=keep">', false)
        ->assertDontSee('https://example.com/route', false);
});

<?php

use ThreeOhEight\Seo\Seo;

it('preserves current behavior with empty strip list', function () {
    $seo = $this->makeSeo(['auto_canonical' => true, 'canonical_strip' => []]);

    $this->get('/test-page?utm_source=google&page=2');
    $html = $seo->renderMeta()->toHtml();

    // With empty strip list, uses url()->current() which excludes query string
    expect($html)->toContain('rel="canonical"')
        ->and($html)->not->toContain('utm_source');
});

it('strips utm params via wildcard', function () {
    $seo = $this->makeSeo(['auto_canonical' => true, 'canonical_strip' => ['utm_*']]);

    $this->get('/pricing?utm_source=google&utm_medium=cpc&page=2');
    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('page=2')
        ->and($html)->not->toContain('utm_source')
        ->and($html)->not->toContain('utm_medium');
});

it('strips exact param names', function () {
    $seo = $this->makeSeo(['auto_canonical' => true, 'canonical_strip' => ['fbclid', 'gclid']]);

    $this->get('/page?fbclid=abc&gclid=xyz&page=3');
    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('page=3')
        ->and($html)->not->toContain('fbclid')
        ->and($html)->not->toContain('gclid');
});

it('keeps non-matching params', function () {
    $seo = $this->makeSeo(['auto_canonical' => true, 'canonical_strip' => ['utm_*']]);

    $this->get('/products?category=tools&page=2&utm_source=newsletter');
    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('category=tools')
        ->and($html)->toContain('page=2');
});

it('strips per-page params via canonicalWithout', function () {
    $seo = $this->makeSeo(['auto_canonical' => true, 'canonical_strip' => ['utm_*']]);
    $seo->canonicalWithout(['sort', 'filter']);

    $this->get('/products?sort=price&filter=active&page=1');
    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('page=1')
        ->and($html)->not->toContain('sort=')
        ->and($html)->not->toContain('filter=');
});

it('combines config and per-page strip patterns', function () {
    $seo = $this->makeSeo(['auto_canonical' => true, 'canonical_strip' => ['utm_*']]);
    $seo->canonicalWithout(['ref']);

    $this->get('/page?utm_source=google&ref=partner&id=42');
    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('id=42')
        ->and($html)->not->toContain('utm_source')
        ->and($html)->not->toContain('ref=partner');
});

it('never strips manual canonical', function () {
    $seo = $this->makeSeo(['auto_canonical' => true, 'canonical_strip' => ['utm_*']]);
    $seo->canonical('https://example.com/page?utm_source=keep');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('href="https://example.com/page?utm_source=keep"');
});

it('returns clean url when all params stripped', function () {
    $seo = $this->makeSeo(['auto_canonical' => true, 'canonical_strip' => ['utm_*', 'fbclid']]);

    $this->get('/page?utm_source=google&fbclid=abc');
    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('rel="canonical"')
        ->and($html)->not->toContain('?');
});

it('returns self from canonicalWithout for chaining', function () {
    $seo = $this->makeSeo();

    expect($seo->canonicalWithout(['sort']))->toBeInstanceOf(Seo::class);
});

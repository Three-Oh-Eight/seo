<?php

use ThreeOhEight\Seo\Seo;

it('renders single preconnect', function () {
    $seo = $this->makeSeo();
    $seo->preconnect('https://cdn.example.com');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('<link rel="preconnect" href="https://cdn.example.com">');
});

it('renders preconnect with crossorigin', function () {
    $seo = $this->makeSeo();
    $seo->preconnect('https://fonts.googleapis.com', crossorigin: true);

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>');
});

it('renders multiple preconnects', function () {
    $seo = $this->makeSeo();
    $seo->preconnect('https://cdn.example.com')
        ->preconnect('https://api.example.com');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('href="https://cdn.example.com"')
        ->and($html)->toContain('href="https://api.example.com"');
});

it('renders single dns-prefetch', function () {
    $seo = $this->makeSeo();
    $seo->dnsPrefetch('https://js.stripe.com');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('<link rel="dns-prefetch" href="https://js.stripe.com">');
});

it('renders multiple dns-prefetches', function () {
    $seo = $this->makeSeo();
    $seo->dnsPrefetch('https://js.stripe.com')
        ->dnsPrefetch('https://www.google-analytics.com');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('href="https://js.stripe.com"')
        ->and($html)->toContain('href="https://www.google-analytics.com"');
});

it('renders both preconnect and dns-prefetch', function () {
    $seo = $this->makeSeo();
    $seo->preconnect('https://cdn.example.com')
        ->dnsPrefetch('https://js.stripe.com');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('rel="preconnect"')
        ->and($html)->toContain('rel="dns-prefetch"');
});

it('returns self for chaining', function () {
    $seo = $this->makeSeo();

    expect($seo->preconnect('https://cdn.example.com'))->toBeInstanceOf(Seo::class)
        ->and($seo->dnsPrefetch('https://js.stripe.com'))->toBeInstanceOf(Seo::class);
});

it('renders no resource hints when none set', function () {
    $seo = $this->makeSeo();

    $html = $seo->renderMeta()->toHtml();

    expect($html)->not->toContain('preconnect')
        ->and($html)->not->toContain('dns-prefetch');
});

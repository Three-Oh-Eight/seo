<?php

use ThreeOhEight\Seo\Seo;

it('renders a single alternate link', function () {
    $seo = $this->makeSeo();
    $seo->alternate('en', 'https://example.com/en');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('<link rel="alternate" hreflang="en" href="https://example.com/en">');
});

it('renders multiple alternate links', function () {
    $seo = $this->makeSeo();
    $seo->alternate('en', 'https://example.com/en')
        ->alternate('nl', 'https://example.com/nl');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('hreflang="en" href="https://example.com/en"')
        ->and($html)->toContain('hreflang="nl" href="https://example.com/nl"');
});

it('renders bulk alternates', function () {
    $seo = $this->makeSeo();
    $seo->alternates([
        'en' => 'https://example.com/en',
        'nl' => 'https://example.com/nl',
        'x-default' => 'https://example.com',
    ]);

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('hreflang="en"')
        ->and($html)->toContain('hreflang="nl"')
        ->and($html)->toContain('hreflang="x-default"');
});

it('merges bulk and single alternates', function () {
    $seo = $this->makeSeo();
    $seo->alternates(['en' => 'https://example.com/en'])
        ->alternate('de', 'https://example.com/de');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('hreflang="en"')
        ->and($html)->toContain('hreflang="de"');
});

it('escapes hreflang urls', function () {
    $seo = $this->makeSeo();
    $seo->alternate('en', 'https://example.com/page?lang=en&region=eu');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('href="https://example.com/page?lang=en&amp;region=eu"');
});

it('returns self for chaining', function () {
    $seo = $this->makeSeo();

    expect($seo->alternate('en', 'https://example.com/en'))->toBeInstanceOf(Seo::class)
        ->and($seo->alternates(['nl' => 'https://example.com/nl']))->toBeInstanceOf(Seo::class);
});

it('renders no alternates when none set', function () {
    $seo = $this->makeSeo();

    $html = $seo->renderMeta()->toHtml();

    expect($html)->not->toContain('hreflang');
});

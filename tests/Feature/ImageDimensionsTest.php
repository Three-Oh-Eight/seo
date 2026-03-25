<?php

use ThreeOhEight\Seo\Seo;

it('renders image without dimensions (backward compat)', function () {
    $seo = $this->makeSeo();
    $seo->image('https://example.com/img.jpg');

    $html = $seo->renderOpenGraph()->toHtml();

    expect($html)->toContain('property="og:image" content="https://example.com/img.jpg"')
        ->and($html)->not->toContain('og:image:width')
        ->and($html)->not->toContain('og:image:height');
});

it('renders image with width and height', function () {
    $seo = $this->makeSeo();
    $seo->image('https://example.com/img.jpg', width: 1200, height: 630);

    $html = $seo->renderOpenGraph()->toHtml();

    expect($html)->toContain('property="og:image" content="https://example.com/img.jpg"')
        ->and($html)->toContain('property="og:image:width" content="1200"')
        ->and($html)->toContain('property="og:image:height" content="630"');
});

it('renders width alone', function () {
    $seo = $this->makeSeo();
    $seo->image('https://example.com/img.jpg', width: 1200);

    $html = $seo->renderOpenGraph()->toHtml();

    expect($html)->toContain('og:image:width')
        ->and($html)->not->toContain('og:image:height');
});

it('renders height alone', function () {
    $seo = $this->makeSeo();
    $seo->image('https://example.com/img.jpg', height: 630);

    $html = $seo->renderOpenGraph()->toHtml();

    expect($html)->toContain('og:image:height')
        ->and($html)->not->toContain('og:image:width');
});

it('renders image type', function () {
    $seo = $this->makeSeo();
    $seo->image('https://example.com/img.png', type: 'image/png');

    $html = $seo->renderOpenGraph()->toHtml();

    expect($html)->toContain('property="og:image:type" content="image/png"');
});

it('renders image alt', function () {
    $seo = $this->makeSeo();
    $seo->image('https://example.com/img.jpg', alt: 'Dashboard preview');

    $html = $seo->renderOpenGraph()->toHtml();

    expect($html)->toContain('property="og:image:alt" content="Dashboard preview"');
});

it('renders all image properties together', function () {
    $seo = $this->makeSeo();
    $seo->image('https://example.com/img.png', width: 1200, height: 630, type: 'image/png', alt: 'Preview');

    $html = $seo->renderOpenGraph()->toHtml();

    expect($html)->toContain('og:image" content="https://example.com/img.png"')
        ->and($html)->toContain('og:image:width" content="1200"')
        ->and($html)->toContain('og:image:height" content="630"')
        ->and($html)->toContain('og:image:type" content="image/png"')
        ->and($html)->toContain('og:image:alt" content="Preview"');
});

it('does not render dimensions for default og_image from config', function () {
    $seo = $this->makeSeo(['og_image' => '/default.png']);

    $html = $seo->renderOpenGraph()->toHtml();

    expect($html)->toContain('og:image" content="/default.png"')
        ->and($html)->not->toContain('og:image:width')
        ->and($html)->not->toContain('og:image:height');
});

it('returns self for chaining', function () {
    $seo = $this->makeSeo();

    expect($seo->image('https://example.com/img.jpg', width: 1200, height: 630))->toBeInstanceOf(Seo::class);
});

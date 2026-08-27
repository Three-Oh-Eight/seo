<?php

it('skips separator formatting with exact title', function () {
    $seo = $this->makeSeo();

    $html = $seo->title('Standalone', exact: true)->render()->toHtml();

    expect($html)->toContain('<title>Standalone</title>')
        ->toContain('<meta property="og:title" content="Standalone">')
        ->toContain('<meta name="twitter:title" content="Standalone">');
});

it('formats title with separator by default', function () {
    $seo = $this->makeSeo();

    $html = $seo->title('Page')->render()->toHtml();

    expect($html)->toContain('<title>Page - TestSite</title>')
        ->toContain('<meta property="og:title" content="Page - TestSite">');
});

it('falls back to the config default title in og and twitter titles', function () {
    $seo = $this->makeSeo(['title' => 'Default Title']);

    $html = $seo->render()->toHtml();

    expect($html)->toContain('<title>Default Title - TestSite</title>')
        ->toContain('<meta property="og:title" content="Default Title - TestSite">')
        ->toContain('<meta name="twitter:title" content="Default Title - TestSite">');
});

it('keeps explicit og and twitter titles verbatim', function () {
    $seo = $this->makeSeo();
    $seo->title('Page', exact: true)->og()->title('OG Verbatim');

    $html = $seo->render()->toHtml();

    expect($html)->toContain('<title>Page</title>')
        ->toContain('<meta property="og:title" content="OG Verbatim">');
});

it('applies conditional callbacks via when and unless', function () {
    $seo = $this->makeSeo();

    $seo->when(true, fn ($seo) => $seo->noindex())
        ->unless(true, fn ($seo) => $seo->title('Should Not Apply'));

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('<meta name="robots" content="noindex, nofollow">')
        ->toContain('<title>TestSite</title>');
});

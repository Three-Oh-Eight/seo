<?php

use ThreeOhEight\Seo\OpenGraphProxy;
use ThreeOhEight\Seo\Seo;
use ThreeOhEight\Seo\SeoData;

it('sets ogTitle on SeoData', function () {
    $data = new SeoData;
    $seo = $this->makeSeo();
    $proxy = new OpenGraphProxy($data, $seo);

    $proxy->title('OG Title');

    expect($data->ogTitle)->toBe('OG Title');
});

it('sets ogDescription on SeoData', function () {
    $data = new SeoData;
    $seo = $this->makeSeo();
    $proxy = new OpenGraphProxy($data, $seo);

    $proxy->description('OG Desc');

    expect($data->ogDescription)->toBe('OG Desc');
});

it('sets ogType on SeoData and renders it', function () {
    $seo = $this->makeSeo();

    $html = $seo->og()->type('article')->renderOpenGraph()->toHtml();

    expect($html)->toContain('<meta property="og:type" content="article">');
});

it('returns Seo instance for chaining', function () {
    $data = new SeoData;
    $seo = $this->makeSeo();
    $proxy = new OpenGraphProxy($data, $seo);

    $result = $proxy->title('OG Title');

    expect($result)->toBeInstanceOf(Seo::class);
});

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

it('returns Seo instance for chaining', function () {
    $data = new SeoData;
    $seo = $this->makeSeo();
    $proxy = new OpenGraphProxy($data, $seo);

    $result = $proxy->title('OG Title');

    expect($result)->toBeInstanceOf(Seo::class);
});

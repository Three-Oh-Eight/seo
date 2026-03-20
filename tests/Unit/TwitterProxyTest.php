<?php

use ThreeOhEight\Seo\Seo;
use ThreeOhEight\Seo\SeoData;
use ThreeOhEight\Seo\TwitterProxy;

it('sets twitterTitle on SeoData', function () {
    $data = new SeoData;
    $seo = $this->makeSeo();
    $proxy = new TwitterProxy($data, $seo);

    $proxy->title('Twitter Title');

    expect($data->twitterTitle)->toBe('Twitter Title');
});

it('sets twitterDescription on SeoData', function () {
    $data = new SeoData;
    $seo = $this->makeSeo();
    $proxy = new TwitterProxy($data, $seo);

    $proxy->description('Twitter Desc');

    expect($data->twitterDescription)->toBe('Twitter Desc');
});

it('returns Seo instance for chaining', function () {
    $data = new SeoData;
    $seo = $this->makeSeo();
    $proxy = new TwitterProxy($data, $seo);

    $result = $proxy->title('Twitter Title');

    expect($result)->toBeInstanceOf(Seo::class);
});

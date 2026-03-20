<?php

use ThreeOhEight\Seo\JsonLd\JsonLdCollection;
use ThreeOhEight\Seo\SeoData;

it('initializes all string properties as null', function () {
    $data = new SeoData;

    expect($data->title)->toBeNull()
        ->and($data->description)->toBeNull()
        ->and($data->robots)->toBeNull()
        ->and($data->canonical)->toBeNull()
        ->and($data->image)->toBeNull()
        ->and($data->ogTitle)->toBeNull()
        ->and($data->ogDescription)->toBeNull()
        ->and($data->twitterTitle)->toBeNull()
        ->and($data->twitterDescription)->toBeNull()
        ->and($data->prev)->toBeNull()
        ->and($data->next)->toBeNull();
});

it('initializes meta array as empty', function () {
    $data = new SeoData;

    expect($data->meta)->toBe([]);
});

it('initializes jsonLd as empty collection', function () {
    $data = new SeoData;

    expect($data->jsonLd)->toBeInstanceOf(JsonLdCollection::class)
        ->and($data->jsonLd->isEmpty())->toBeTrue();
});

it('allows property assignment', function () {
    $data = new SeoData;
    $data->title = 'My Page';
    $data->description = 'A description';

    expect($data->title)->toBe('My Page')
        ->and($data->description)->toBe('A description');
});

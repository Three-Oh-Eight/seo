<?php

use ThreeOhEight\Seo\JsonLd\JsonLdBlock;
use ThreeOhEight\Seo\JsonLd\JsonLdCollection;

it('starts empty', function () {
    $collection = new JsonLdCollection;

    expect($collection->isEmpty())->toBeTrue();
});

it('is not empty after adding a block', function () {
    $collection = new JsonLdCollection;
    $collection->add(JsonLdBlock::make('Organization'));

    expect($collection->isEmpty())->toBeFalse();
});

it('renders empty string when empty', function () {
    $collection = new JsonLdCollection;

    expect($collection->render())->toBe('');
});

it('renders single block without @graph', function () {
    $collection = new JsonLdCollection;
    $collection->add(JsonLdBlock::make('Organization')->title('Acme'));

    $json = $collection->render();
    $data = json_decode(strip_tags($json), true);

    expect($data)->toHaveKey('@context', 'https://schema.org')
        ->and($data)->toHaveKey('@type', 'Organization')
        ->and($data)->toHaveKey('name', 'Acme')
        ->and($data)->not->toHaveKey('@graph');
});

it('renders multiple blocks with @graph', function () {
    $collection = new JsonLdCollection;
    $collection->add(JsonLdBlock::make('Organization')->title('Acme'));
    $collection->add(JsonLdBlock::make('Person')->title('Alice'));

    $json = $collection->render();
    $data = json_decode(strip_tags($json), true);

    expect($data)->toHaveKey('@context', 'https://schema.org')
        ->and($data)->toHaveKey('@graph')
        ->and($data['@graph'])->toHaveCount(2)
        ->and($data['@graph'][0]['@type'])->toBe('Organization')
        ->and($data['@graph'][1]['@type'])->toBe('Person');
});

it('wraps in script tag', function () {
    $collection = new JsonLdCollection;
    $collection->add(JsonLdBlock::make('Organization'));

    $html = $collection->render();

    expect($html)->toStartWith('<script type="application/ld+json">')
        ->and($html)->toEndWith('</script>');
});

it('uses unescaped slashes and unicode', function () {
    $collection = new JsonLdCollection;
    $collection->add(JsonLdBlock::make('Organization')->value('url', 'https://example.com/path'));

    $html = $collection->render();

    expect($html)->toContain('https://example.com/path')
        ->and($html)->not->toContain('https:\\/\\/');
});

it('renders valid JSON', function () {
    $collection = new JsonLdCollection;
    $collection->add(JsonLdBlock::make('Organization')->title('Acme & Co'));

    $html = $collection->render();
    $jsonString = str_replace(['<script type="application/ld+json">', '</script>'], '', $html);
    $decoded = json_decode($jsonString, true);

    expect($decoded)->not->toBeNull()
        ->and($decoded['name'])->toBe('Acme & Co');
});

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

// --- Separate blocks ---

it('is not empty after adding a separate block', function () {
    $collection = new JsonLdCollection;
    $collection->addSeparate(JsonLdBlock::make('QAPage'));

    expect($collection->isEmpty())->toBeFalse();
});

it('renders separate block as individual script tag', function () {
    $collection = new JsonLdCollection;
    $collection->addSeparate(JsonLdBlock::make('QAPage')->value('mainEntity', 'test'));

    $html = $collection->render();

    expect($html)->toContain('<script type="application/ld+json">')
        ->and($html)->toContain('"@type":"QAPage"')
        ->and($html)->toContain('"@context":"https://schema.org"');
});

it('renders multiple separate blocks as individual script tags', function () {
    $collection = new JsonLdCollection;
    $collection->addSeparate(JsonLdBlock::make('QAPage')->title('Q1'));
    $collection->addSeparate(JsonLdBlock::make('QAPage')->title('Q2'));

    $html = $collection->render();
    $scriptCount = substr_count($html, '<script type="application/ld+json">');

    expect($scriptCount)->toBe(2)
        ->and($html)->toContain('"name":"Q1"')
        ->and($html)->toContain('"name":"Q2"')
        ->and($html)->not->toContain('@graph');
});

it('renders regular and separate blocks together', function () {
    $collection = new JsonLdCollection;
    $collection->add(JsonLdBlock::make('Organization')->title('Acme'));
    $collection->addSeparate(JsonLdBlock::make('QAPage')->title('Q1'));
    $collection->addSeparate(JsonLdBlock::make('QAPage')->title('Q2'));

    $html = $collection->render();
    $scriptCount = substr_count($html, '<script type="application/ld+json">');

    // 1 for the Organization + 2 separate for QAPages
    expect($scriptCount)->toBe(3);
});

it('renders regular blocks as graph alongside separate blocks', function () {
    $collection = new JsonLdCollection;
    $collection->add(JsonLdBlock::make('Organization')->title('Acme'));
    $collection->add(JsonLdBlock::make('WebSite')->title('Site'));
    $collection->addSeparate(JsonLdBlock::make('QAPage')->title('Q1'));

    $html = $collection->render();

    // Regular blocks in @graph, separate as individual
    expect($html)->toContain('"@graph"')
        ->and(substr_count($html, '<script type="application/ld+json">'))->toBe(2);
});

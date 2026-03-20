<?php

use ThreeOhEight\Seo\JsonLd\JsonLdBlock;

it('sets @type via constructor', function () {
    $block = new JsonLdBlock('Organization');

    expect($block->toArray())->toHaveKey('@type', 'Organization');
});

it('creates via static make factory', function () {
    $block = JsonLdBlock::make('Person');

    expect($block->toArray())->toHaveKey('@type', 'Person');
});

it('sets name via title()', function () {
    $block = JsonLdBlock::make('Organization')->title('Acme');

    expect($block->toArray())->toHaveKey('name', 'Acme');
});

it('sets description via description()', function () {
    $block = JsonLdBlock::make('Organization')->description('A company');

    expect($block->toArray())->toHaveKey('description', 'A company');
});

it('sets arbitrary keys via value()', function () {
    $block = JsonLdBlock::make('Organization')->value('url', 'https://example.com');

    expect($block->toArray())->toHaveKey('url', 'https://example.com');
});

it('supports fluent chaining', function () {
    $block = JsonLdBlock::make('Organization')
        ->title('Acme')
        ->description('A company')
        ->value('url', 'https://example.com');

    $array = $block->toArray();

    expect($array['@type'])->toBe('Organization')
        ->and($array['name'])->toBe('Acme')
        ->and($array['description'])->toBe('A company')
        ->and($array['url'])->toBe('https://example.com');
});

it('resolves nested JsonLdBlock values', function () {
    $address = JsonLdBlock::make('PostalAddress')->value('city', 'Rotterdam');
    $block = JsonLdBlock::make('Organization')->value('address', $address);

    $array = $block->toArray();

    expect($array['address'])->toBe([
        '@type' => 'PostalAddress',
        'city' => 'Rotterdam',
    ]);
});

it('resolves arrays of JsonLdBlock values', function () {
    $member1 = JsonLdBlock::make('Person')->title('Alice');
    $member2 = JsonLdBlock::make('Person')->title('Bob');
    $block = JsonLdBlock::make('Organization')->value('member', [$member1, $member2]);

    $array = $block->toArray();

    expect($array['member'])->toBe([
        ['@type' => 'Person', 'name' => 'Alice'],
        ['@type' => 'Person', 'name' => 'Bob'],
    ]);
});

it('leaves non-block values untouched', function () {
    $block = JsonLdBlock::make('Organization')
        ->value('name', 'Acme')
        ->value('foundingDate', '2020-01-01')
        ->value('tags', ['tech', 'saas']);

    $array = $block->toArray();

    expect($array['name'])->toBe('Acme')
        ->and($array['foundingDate'])->toBe('2020-01-01')
        ->and($array['tags'])->toBe(['tech', 'saas']);
});

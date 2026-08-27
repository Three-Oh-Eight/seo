<?php

use ThreeOhEight\Seo\Routing\RouteSeo;

it('passes plain scalar fields through', function () {
    expect(RouteSeo::normalize([
        'title' => 'Pricing',
        'description' => 'Desc',
        'robots' => 'noarchive',
        'canonical' => 'https://example.com',
        'og_type' => 'article',
    ]))->toBe([
        'title' => 'Pricing',
        'description' => 'Desc',
        'robots' => 'noarchive',
        'canonical' => 'https://example.com',
        'og_type' => 'article',
    ]);
});

it('takes the last element of merge-recursive artifact lists', function () {
    expect(RouteSeo::normalize(['title' => ['Outer', 'Inner']]))
        ->toBe(['title' => 'Inner']);
});

it('drops empty strings and empty lists', function () {
    expect(RouteSeo::normalize(['title' => '', 'description' => []]))->toBe([]);
});

it('ignores unknown keys', function () {
    expect(RouteSeo::normalize(['bogus' => 'value']))->toBe([]);
});

it('folds noindex true into robots', function () {
    expect(RouteSeo::normalize(['noindex' => true]))
        ->toBe(['robots' => 'noindex, nofollow']);
});

it('does not fold noindex when robots is set explicitly', function () {
    expect(RouteSeo::normalize(['noindex' => true, 'robots' => 'noarchive']))
        ->toBe(['robots' => 'noarchive']);
});

it('handles noindex artifact lists from nested groups', function () {
    expect(RouteSeo::normalize(['noindex' => [false, true]]))
        ->toBe(['robots' => 'noindex, nofollow']);
});

it('ignores noindex false', function () {
    expect(RouteSeo::normalize(['noindex' => false]))->toBe([]);
});

it('returns an empty array for empty input', function () {
    expect(RouteSeo::normalize([]))->toBe([]);
});

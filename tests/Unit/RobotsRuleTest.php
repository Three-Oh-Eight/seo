<?php

use ThreeOhEight\Seo\RobotsRule;
use ThreeOhEight\Seo\Seo;

it('has lowercase directive values', function () {
    expect(RobotsRule::NoIndex->value)->toBe('noindex')
        ->and(RobotsRule::NoFollow->value)->toBe('nofollow')
        ->and(RobotsRule::All->value)->toBe('all')
        ->and(RobotsRule::None->value)->toBe('none')
        ->and(RobotsRule::NoArchive->value)->toBe('noarchive')
        ->and(RobotsRule::NoSnippet->value)->toBe('nosnippet')
        ->and(RobotsRule::NoImageIndex->value)->toBe('noimageindex');
});

it('converts a plain string', function () {
    expect(Seo::robotsToString('noindex, nofollow'))->toBe('noindex, nofollow');
});

it('converts a single enum', function () {
    expect(Seo::robotsToString(RobotsRule::NoIndex))->toBe('noindex');
});

it('converts a mixed array of enums and strings', function () {
    expect(Seo::robotsToString([RobotsRule::NoIndex, 'nofollow', RobotsRule::NoArchive]))
        ->toBe('noindex, nofollow, noarchive');
});

it('accepts enums on the robots method', function () {
    $seo = $this->makeSeo();

    $html = $seo->robots([RobotsRule::NoIndex, RobotsRule::NoFollow])->renderMeta()->toHtml();

    expect($html)->toContain('<meta name="robots" content="noindex, nofollow">');
});

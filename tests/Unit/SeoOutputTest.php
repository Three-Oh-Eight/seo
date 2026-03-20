<?php

use ThreeOhEight\Seo\SeoOutput;

it('returns html string via toHtml', function () {
    $output = new SeoOutput('<title>Test</title>');

    expect($output->toHtml())->toBe('<title>Test</title>');
});

it('is castable to string', function () {
    $output = new SeoOutput('<title>Test</title>');

    expect((string) $output)->toBe('<title>Test</title>');
});

it('handles empty string', function () {
    $output = new SeoOutput('');

    expect($output->toHtml())->toBe('')
        ->and((string) $output)->toBe('');
});

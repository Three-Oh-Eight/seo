<?php

use Illuminate\Support\Facades\Route;
use ThreeOhEight\Seo\Seo;

it('applies noindex via middleware', function () {
    Route::middleware('seo:noindex')->get('/admin', fn () => app(Seo::class)->renderMeta()->toHtml());

    $response = $this->get('/admin');

    $response->assertSee('<meta name="robots" content="noindex">', false);
});

it('applies noindex nofollow via middleware', function () {
    Route::middleware('seo:noindex,nofollow')->get('/login', fn () => app(Seo::class)->renderMeta()->toHtml());

    $response = $this->get('/login');

    $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
});

it('applies custom robots string via middleware', function () {
    Route::middleware('seo:noarchive')->get('/private', fn () => app(Seo::class)->renderMeta()->toHtml());

    $response = $this->get('/private');

    $response->assertSee('<meta name="robots" content="noarchive">', false);
});

it('does not render robots without middleware', function () {
    Route::get('/public', fn () => app(Seo::class)->renderMeta()->toHtml());

    $response = $this->get('/public');

    $response->assertDontSee('name="robots"', false);
});

it('works with route groups', function () {
    Route::middleware('seo:noindex,nofollow')->group(function () {
        Route::get('/app/dashboard', fn () => app(Seo::class)->renderMeta()->toHtml());
        Route::get('/app/settings', fn () => app(Seo::class)->renderMeta()->toHtml());
    });

    $this->get('/app/dashboard')->assertSee('content="noindex, nofollow"', false);
    $this->get('/app/settings')->assertSee('content="noindex, nofollow"', false);
});

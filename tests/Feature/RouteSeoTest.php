<?php

use Illuminate\Support\Facades\Route;
use ThreeOhEight\Seo\RobotsRule;
use ThreeOhEight\Seo\Seo;

it('writes snake_case keys into the route action and stays chainable', function () {
    $route = Route::get('/pricing', fn () => 'ok')
        ->seo(title: 'Pricing', ogType: 'article')
        ->name('pricing');

    expect($route->getAction('seo'))->toBe(['title' => 'Pricing', 'og_type' => 'article'])
        ->and($route->getName())->toBe('pricing');
});

it('renders route-level title, description, robots and og type', function () {
    Route::get('/pricing', fn () => app(Seo::class)->render()->toHtml())
        ->seo(title: 'Pricing', description: 'Route desc', robots: 'noarchive', ogType: 'article');

    $response = $this->get('/pricing');

    $response->assertSee('<title>Pricing - Laravel</title>', false);
    $response->assertSee('<meta name="description" content="Route desc">', false);
    $response->assertSee('<meta name="robots" content="noarchive">', false);
    $response->assertSee('<meta property="og:type" content="article">', false);
});

it('renders a route-level canonical', function () {
    Route::get('/pricing', fn () => app(Seo::class)->renderMeta()->toHtml())
        ->seo(canonical: 'https://example.com/pricing');

    $this->get('/pricing')
        ->assertSee('<link rel="canonical" href="https://example.com/pricing">', false);
});

it('lets runtime values beat route values field by field', function () {
    Route::get('/pricing', function () {
        app(Seo::class)->title('Runtime');

        return app(Seo::class)->renderMeta()->toHtml();
    })->seo(title: 'Route', description: 'Route desc');

    $response = $this->get('/pricing');

    $response->assertSee('<title>Runtime - Laravel</title>', false);
    $response->assertSee('<meta name="description" content="Route desc">', false);
});

it('lets route values beat config defaults', function () {
    config(['seo.description' => 'Config desc']);

    Route::get('/pricing', fn () => app(Seo::class)->renderMeta()->toHtml())
        ->seo(description: 'Route desc');

    $this->get('/pricing')
        ->assertSee('<meta name="description" content="Route desc">', false);
});

it('folds noindex into robots', function () {
    Route::get('/internal', fn () => app(Seo::class)->renderMeta()->toHtml())
        ->seo(noindex: true);

    $this->get('/internal')
        ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
});

it('lets an explicit robots value beat the noindex flag', function () {
    Route::get('/internal', fn () => app(Seo::class)->renderMeta()->toHtml())
        ->seo(robots: 'noarchive', noindex: true);

    $this->get('/internal')
        ->assertSee('<meta name="robots" content="noarchive">', false);
});

it('accepts a RobotsRule enum for robots', function () {
    Route::get('/one', fn () => app(Seo::class)->renderMeta()->toHtml())
        ->seo(robots: RobotsRule::NoIndex);

    $this->get('/one')->assertSee('content="noindex"', false);
});

it('accepts a mixed array of enums and strings for robots', function () {
    Route::get('/two', fn () => app(Seo::class)->renderMeta()->toHtml())
        ->seo(robots: [RobotsRule::NoIndex, 'nofollow']);

    $this->get('/two')->assertSee('content="noindex, nofollow"', false);
});

it('propagates seo group attributes to child routes', function () {
    Route::group(['seo' => ['description' => 'Group desc']], function () {
        Route::get('/a', fn () => app(Seo::class)->renderMeta()->toHtml());
        Route::get('/b', fn () => app(Seo::class)->renderMeta()->toHtml());
    });

    $this->get('/a')->assertSee('content="Group desc"', false);
    $this->get('/b')->assertSee('content="Group desc"', false);
});

it('lets route-level seo override group values per field', function () {
    Route::group(['seo' => ['title' => 'Group', 'description' => 'Group desc']], function () {
        Route::get('/page', fn () => app(Seo::class)->renderMeta()->toHtml())
            ->seo(title: 'Route');
    });

    $response = $this->get('/page');

    $response->assertSee('<title>Route - Laravel</title>', false);
    $response->assertSee('content="Group desc"', false);
});

it('lets the innermost nested group win', function () {
    Route::group(['seo' => ['description' => 'Outer']], function () {
        Route::group(['seo' => ['description' => 'Inner']], function () {
            Route::get('/nested', fn () => app(Seo::class)->renderMeta()->toHtml());
        });
    });

    $this->get('/nested')->assertSee('content="Inner"', false);
});

it('survives route caching', function () {
    $this->defineCacheRoutes(<<<'PHP'
<?php

use Illuminate\Support\Facades\Route;
use ThreeOhEight\Seo\Tests\Support\RenderMetaController;

Route::get('/cached-pricing', RenderMetaController::class)
    ->seo(title: 'Pricing', robots: 'noarchive');
PHP);

    $response = $this->get('/cached-pricing');

    $response->assertSee('<title>Pricing - Laravel</title>', false);
    $response->assertSee('<meta name="robots" content="noarchive">', false);
});

it('does not error without a matched route', function () {
    $html = $this->makeSeo()->renderMeta()->toHtml();

    expect($html)->toContain('<title>TestSite</title>');
});

it('lets middleware robots beat route robots', function () {
    Route::middleware('seo:noindex')->get('/admin', fn () => app(Seo::class)->renderMeta()->toHtml())
        ->seo(robots: 'noarchive');

    $this->get('/admin')->assertSee('<meta name="robots" content="noindex">', false);
});

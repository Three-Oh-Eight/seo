<?php

use ThreeOhEight\Seo\Seo;

it('renders all tags via @seo directive', function () {
    app(Seo::class)->title('Directive Test');

    $view = $this->blade('@seo');

    $view->assertSee('<title>Directive Test', false);
    $view->assertSee('og:title', false);
    $view->assertSee('twitter:title', false);
});

it('renders all tags via x-seo::tags component', function () {
    app(Seo::class)->title('Component Test');

    $view = $this->blade('<x-seo::tags />');

    $view->assertSee('<title>Component Test', false);
    $view->assertSee('og:title', false);
    $view->assertSee('twitter:title', false);
});

it('renders meta only via x-seo::meta', function () {
    app(Seo::class)->title('Meta Only');

    $view = $this->blade('<x-seo::meta />');

    $view->assertSee('<title>Meta Only', false);
    $view->assertDontSee('og:title', false);
});

it('renders opengraph only via x-seo::opengraph', function () {
    app(Seo::class)->title('OG Only');

    $view = $this->blade('<x-seo::opengraph />');

    $view->assertSee('og:title', false);
    $view->assertDontSee('<title>', false);
});

it('renders twitter only via x-seo::twitter', function () {
    app(Seo::class)->title('TW Only');

    $view = $this->blade('<x-seo::twitter />');

    $view->assertSee('twitter:title', false);
    $view->assertDontSee('<title>', false);
});

it('renders json-ld only via x-seo::json-ld', function () {
    app(Seo::class)->jsonLd('Organization')->title('Acme');

    $view = $this->blade('<x-seo::json-ld />');

    $view->assertSee('application/ld+json', false);
    $view->assertDontSee('<title>', false);
});

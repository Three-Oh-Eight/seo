<?php

use Laravel\Head\Facades\Head;

it('renders the config defaults through @head', function () {
    $this->headRoute('/plain');

    $this->get('/plain')
        ->assertOk()
        ->assertSee('<title>TestSite</title>', false)
        ->assertSee('<meta name="description" content="Default description">', false)
        ->assertSee('<link rel="canonical" href="https://localhost/plain">', false)
        ->assertSee('<meta name="robots" content="index, follow">', false)
        ->assertSee('<meta property="og:type" content="website">', false)
        ->assertSee('<meta property="og:site_name" content="TestSite">', false)
        ->assertSee('<meta property="og:title" content="TestSite">', false)
        ->assertSee('<meta property="og:image" content="https://cdn.test/og.png">', false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
        ->assertSee('<meta name="twitter:site" content="@testsite">', false)
        ->assertSee('<meta name="twitter:image" content="https://cdn.test/og.png">', false);
});

it('composes the configured fallback title with the site name', function () {
    $this->withSeoConfig(['title' => 'Home']);
    $this->headRoute('/plain');

    $this->get('/plain')->assertSee('<title>Home - TestSite</title>', false);
});

it('decorates page titles with the separator and site name', function () {
    $this->withSeoConfig(['separator' => ' | ']);
    $this->headRoute('/page', fn () => Head::title('Pricing'));

    $this->get('/page')
        ->assertSee('<title>Pricing | TestSite</title>', false)
        ->assertSee('<meta property="og:title" content="Pricing | TestSite">', false);
});

it('renders exact titles without the site name', function () {
    $this->headRoute('/page', fn () => Head::title('Just This', exact: true));

    $this->get('/page')->assertSee('<title>Just This</title>', false);
});

it('omits the canonical when auto_canonical is off', function () {
    $this->withSeoConfig(['auto_canonical' => false]);
    $this->headRoute('/plain');

    $this->get('/plain')->assertDontSee('rel="canonical"', false);
});

it('renders a distinct default twitter image', function () {
    $this->withSeoConfig(['twitter_image' => 'https://cdn.test/twitter.png']);
    $this->headRoute('/plain');

    $this->get('/plain')
        ->assertSee('<meta name="twitter:image" content="https://cdn.test/twitter.png">', false)
        ->assertSee('<meta property="og:image" content="https://cdn.test/og.png">', false);
});

it('lets route withHead metadata override the config defaults', function () {
    $this->headRoute('/pricing')->withHead(
        title: 'Pricing',
        description: 'Route description',
        robots: 'noarchive',
        canonical: 'https://example.com/pricing',
        og: ['type' => 'article'],
    );

    $this->get('/pricing')
        ->assertSee('<title>Pricing - TestSite</title>', false)
        ->assertSee('<meta name="description" content="Route description">', false)
        ->assertSee('<meta name="robots" content="noarchive">', false)
        ->assertSee('<link rel="canonical" href="https://example.com/pricing">', false)
        ->assertSee('<meta property="og:type" content="article">', false)
        ->assertSee('<meta property="og:site_name" content="TestSite">', false)
        ->assertDontSee('Default description', false);
});

it('lets runtime Head calls override route metadata', function () {
    $this->headRoute('/pricing', function () {
        Head::title('Runtime')->hiddenFromRobots();
    })->withHead(title: 'Route title', description: 'Route description', robots: 'noarchive');

    $this->get('/pricing')
        ->assertSee('<title>Runtime - TestSite</title>', false)
        ->assertSee('<meta name="description" content="Route description">', false)
        ->assertSee('<meta name="robots" content="none">', false)
        ->assertDontSee('Route title', false)
        ->assertDontSee('noarchive', false);
});

it('lets app defaults beat the config defaults', function () {
    $this->withSeoConfig(['title' => 'Config title', 'description' => 'Config description']);

    Head::defaults(fn ($head) => $head->title('X')->description('Y'));

    $this->headRoute('/plain');

    $this->get('/plain')
        ->assertOk()
        ->assertSee('<title>X</title>', false)
        ->assertSee('<meta name="description" content="Y">', false)
        ->assertDontSee('Config title', false)
        ->assertDontSee('Config description', false);
});

it('keeps the config suffix and separator on page titles when the app sets default titles', function () {
    $this->withSeoConfig(['title' => 'Config title', 'separator' => ' | ']);

    Head::defaults(fn ($head) => $head->title('X')->description('Y'));

    $this->headRoute('/page', fn () => Head::title('Pricing'));

    $this->get('/page')
        ->assertSee('<title>Pricing | TestSite</title>', false)
        ->assertSee('<meta name="description" content="Y">', false);
});

it('lets a runtime og image replace the default og and twitter image', function () {
    $this->headRoute('/post', fn () => Head::ogImage('https://cdn.test/post.png'));

    $this->get('/post')
        ->assertSee('<meta property="og:image" content="https://cdn.test/post.png">', false)
        ->assertSee('<meta name="twitter:image" content="https://cdn.test/post.png">', false)
        ->assertDontSee('https://cdn.test/og.png', false);
});

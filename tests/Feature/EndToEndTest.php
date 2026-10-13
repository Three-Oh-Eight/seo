<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Laravel\Head\Facades\Head;
use Laravel\Head\Facades\Schema;
use ThreeOhEight\Seo\Concerns\HasSeo;
use ThreeOhEight\Seo\Contracts\Seoable;

class SeoEndToEndApp extends Model implements Seoable
{
    use HasSeo;

    protected $guarded = [];
}

it('renders config defaults, route metadata, a HasSeo model and a schema type in one layout', function () {
    $app = new SeoEndToEndApp(['title' => 'Payflo', 'meta_description' => 'Invoicing for freelancers']);

    Route::get('/apps/payflo', function () use ($app): string {
        Head::from($app)->schema(
            Schema::webApplication()
                ->name($app->title)
                ->applicationCategory('BusinessApplication')
                ->operatingSystem('Any')
                ->price(0, 'EUR')
                ->aggregateRating(4.7, count: 52),
        );

        return Blade::render('<!DOCTYPE html><html><head>@head</head><body>{{ $name }}</body></html>', ['name' => $app->title]);
    })->withHead(description: 'Route description', robots: 'noarchive', og: ['type' => 'product']);

    $html = $this->get('/apps/payflo?utm_source=news')->assertOk()->getContent();

    expect($html)
        // model (runtime layer) over route and defaults
        ->toContain('<title>Payflo - TestSite</title>')
        ->toContain('<meta name="description" content="Invoicing for freelancers">')
        ->not->toContain('Route description')
        // route layer over defaults
        ->toContain('<meta name="robots" content="noarchive">')
        ->toContain('<meta property="og:type" content="product">')
        // config defaults
        ->toContain('<link rel="canonical" href="https://localhost/apps/payflo">')
        ->toContain('<meta property="og:site_name" content="TestSite">')
        ->toContain('<meta property="og:image" content="https://cdn.test/og.png">')
        ->toContain('<meta name="twitter:site" content="@testsite">')
        // og:url extension
        ->toContain('<meta property="og:url" content="https://localhost/apps/payflo">');

    expect(jsonLdBlocks($html))->toBe([[
        '@context' => 'https://schema.org',
        '@type' => 'WebApplication',
        'name' => 'Payflo',
        'applicationCategory' => 'BusinessApplication',
        'operatingSystem' => 'Any',
        'offers' => ['@type' => 'Offer', 'price' => 0, 'priceCurrency' => 'EUR'],
        'aggregateRating' => ['@type' => 'AggregateRating', 'ratingValue' => 4.7, 'ratingCount' => 52],
    ]]);
});

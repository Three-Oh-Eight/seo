# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Is

`three_oh_eight/seo` — a lean, Laravel-native SEO tag management package. A pure PHP library with Blade views.

## Package Details

- **Namespace**: `ThreeOhEight\Seo`
- **Requires**: PHP 8.5+, Laravel 12/13 (illuminate/support, illuminate/view)
- **Auto-discovered**: Service provider and `Seo` facade registered via composer.json `extra.laravel`

## Architecture

The package is small (~20 source files) with a clear flow:

1. **`SeoServiceProvider`** registers a scoped `Seo` instance per request, hydrated with `SeoDefaults` from `config/seo.php`; also registers the `Route::seo()` macro and the `seo` middleware alias
2. **`Seo`** (main service) — fluent API to set title, description, robots, canonical, image, OG, Twitter, and JSON-LD. Renders HTML output via `SeoOutput`; `toArray()` exposes all resolved values. Macroable and Conditionable (`when`/`unless`)
3. **`SeoData`** — mutable DTO holding per-page overrides (title, description, robots, canonical, image, OG/Twitter-specific titles/descriptions, JSON-LD blocks)
4. **`SeoDefaults`** — readonly value object for site-wide defaults from config. Fallback when `SeoData` properties are null
5. **Proxies** — `OpenGraphProxy` and `TwitterProxy` allow `Seo::og()->title()` / `Seo::twitter()->title()` for platform-specific overrides, returning the `Seo` instance for chaining
6. **JSON-LD** — `JsonLdBlock` (single schema item) + `JsonLdCollection` (renders single or `@graph` array). Blocks are added via `Seo::jsonLd('Type')`
7. **Blade components** — `<x-seo::tags />` renders everything; individual `<x-seo::meta />`, `<x-seo::opengraph />`, `<x-seo::twitter />`, `<x-seo::json-ld />` also available

## Key Design Decisions

- **Scoped binding**: `Seo` is scoped (not singleton) — fresh instance per request, safe for Octane
- **Cascade fallback**: Runtime data → route-level metadata → defaults from config, resolved field by field
- **Route-level metadata**: `Route::get(...)->seo(title: ..., description: ..., robots: ..., canonical: ..., noindex: ..., ogType: ...)` or `Route::group(['seo' => [...]], ...)` (plain scalars only). Values are stored in the route action and survive `route:cache`
- **Title construction**: Page title + separator + site name (e.g. "Dashboard - Acme"). When no page title, renders site name alone. `title('X', exact: true)` skips the separator formatting
- **Robots**: `robots()` accepts a string, a `RobotsRule` enum case, or a mixed array of both
- **Auto-canonical**: Enabled by default via config; uses `url()->current()`

## Usage in Consuming Apps

```php
// In a controller or Livewire component
Seo::title('Dashboard')->description('Your overview');
Seo::noindex(); // auth pages
Seo::jsonLd('Organization')->title('Acme')->value('url', 'https://acme.test');

// In layout blade
<x-seo::tags />
```

## Config

Publish with `php artisan vendor:publish --tag=seo-config`. All keys in `config/seo.php` map directly to `SeoDefaults` constructor params (snake_case config → camelCase property).

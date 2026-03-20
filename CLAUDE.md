# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Is

`three_oh_eight/seo` — a lean, Laravel-native SEO tag management package. Used across the 308 monorepo apps (PayFlo, ChaosDesk, ClearHead). No tests or build system yet; this is a pure PHP library with Blade views.

## Package Details

- **Namespace**: `ThreeOhEight\Seo`
- **Requires**: PHP 8.5+, Laravel 12/13 (illuminate/support, illuminate/view)
- **Auto-discovered**: Service provider and `Seo` facade registered via composer.json `extra.laravel`

## Architecture

The package is small (~12 files) with a clear flow:

1. **`SeoServiceProvider`** registers a scoped `Seo` instance per request, hydrated with `SeoDefaults` from `config/seo.php`
2. **`Seo`** (main service) — fluent API to set title, description, robots, canonical, image, OG, Twitter, and JSON-LD. Renders HTML output via `SeoOutput`
3. **`SeoData`** — mutable DTO holding per-page overrides (title, description, robots, canonical, image, OG/Twitter-specific titles/descriptions, JSON-LD blocks)
4. **`SeoDefaults`** — readonly value object for site-wide defaults from config. Fallback when `SeoData` properties are null
5. **Proxies** — `OpenGraphProxy` and `TwitterProxy` allow `Seo::og()->title()` / `Seo::twitter()->title()` for platform-specific overrides, returning the `Seo` instance for chaining
6. **JSON-LD** — `JsonLdBlock` (single schema item) + `JsonLdCollection` (renders single or `@graph` array). Blocks are added via `Seo::jsonLd('Type')`
7. **Blade components** — `<x-seo::tags />` renders everything; individual `<x-seo::meta />`, `<x-seo::opengraph />`, `<x-seo::twitter />`, `<x-seo::json-ld />` also available

## Key Design Decisions

- **Scoped binding**: `Seo` is scoped (not singleton) — fresh instance per request, safe for Octane
- **Cascade fallback**: Page-specific data → OG/Twitter-specific data → defaults from config
- **Title construction**: Page title + separator + site name (e.g. "Dashboard - PayFlo"). When no page title, renders site name alone
- **Suffix vs separator**: `suffix` is appended to OG/Twitter titles; `separator` is used for the `<title>` tag
- **Auto-canonical**: Enabled by default via config; uses `url()->current()`

## Usage in Consuming Apps

```php
// In a controller or Livewire component
Seo::title('Dashboard')->description('Your overview');
Seo::noindex(); // auth pages
Seo::jsonLd('Organization')->title('PayFlo')->value('url', 'https://payflo.eu');

// In layout blade
<x-seo::tags />
```

## Config

Publish with `php artisan vendor:publish --tag=seo-config`. All keys in `config/seo.php` map directly to `SeoDefaults` constructor params (snake_case config → camelCase property).

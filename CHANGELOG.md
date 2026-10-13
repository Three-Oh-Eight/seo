# Changelog

## 0.5.0 - unreleased

Rewritten as a thin add-on for [laravel/head](https://github.com/laravel/head). Head now renders the page head and owns the cascade (defaults, then route `withHead()` metadata, then runtime `Head::` calls); this package keeps only what head lacks.

### Added

- `og:url`, rendered from the resolved canonical URL on pages with Open Graph tags (0.4 behaviour; head itself only renders it when set explicitly). Override with `Head::og(url: ...)` or `withHead(og: ['url' => ...])`, turn off per route with `withHead(extensions: ['ogUrl' => false])` or everywhere with the new `seo.og_url` config key.
- Schema builders on head's `Schema` factory: `Schema::softwareApplication()`, `webApplication()`, `qaPage()`, `question()` and `answer()`, covering Google's required and recommended properties. Setters skip null and empty values, so head's schema validator never sees them.
- `Head::from($model)` and `Head::canonicalWithout($params)` macros.

### Changed

- Requires Laravel 13.17+ (`illuminate/*: ^13.17`) and `laravel/head: ^0.2`. Laravel 12 is no longer supported.
- `config/seo.php` keeps its keys and now feeds `Head::defaults()`. `twitter_image` only renders when it differs from `og_image` (head falls back to the og image).
- The automatic canonical always drops the whole query string; `canonical_strip` now applies to `Head::canonicalWithout()` only.
- `Head::canonicalWithout()` now takes precedence over a route-level canonical set with `withHead(canonical: ...)`. In 0.4 the route canonical won.
- `Seoable::toSeo()` and the `WithSeo` component `seo()` method receive `Laravel\Head\HeadManager` instead of `ThreeOhEight\Seo\Seo`.
- JSON-LD renders one `<script>` per schema (head's behaviour) instead of a combined `@graph`.

### Removed

- The `Seo` service and facade, `SeoData`, `SeoDefaults`, `SeoOutput`, the `og()`/`twitter()` proxies, `RobotsRule`, `JsonLdBlock`, `JsonLdCollection`, `JsonLdHelper`, the `@seo` directive, the `<x-seo::*>` components, the `seo` middleware and the route `->seo()` macro and `seo` group attribute. Head covers all of them; see the table below.
- `faqPage()`: Google stopped showing FAQ rich results on 2026-05-07 and removed the documentation on 2026-06-15. The markup is still valid schema.org but no longer earns a rich result. If you want it anyway, use head's `Schema::faq()`.

### Upgrading from 0.4

1. `composer require three_oh_eight/seo:^0.5` (pulls in `laravel/head`).
2. Replace `@seo` or `<x-seo::tags />` in your layout with `@head`.
3. Work through the table. Imports: `ThreeOhEight\Seo\Facades\Seo` becomes `Laravel\Head\Facades\Head`, schema builders come from `Laravel\Head\Facades\Schema`.
4. The automatic canonical now drops the entire query string (0.4 kept everything except the `canonical_strip` patterns). Paginated or filtered pages whose parameters change the content must call `Head::canonicalWithout()` (keeps the query, minus `canonical_strip` and the given patterns) or use head's pagination helper `Head::paginate($paginator)`, otherwise every page variant canonicalises to the bare URL.

| 0.4 | 0.5 (laravel/head) |
|---|---|
| `@seo`, `<x-seo::tags />` | `@head` |
| `<x-seo::meta />`, `<x-seo::opengraph />`, `<x-seo::twitter />`, `<x-seo::json-ld />` | `@head` (no per-section rendering) |
| `Seo::render()`, `renderMeta()`, `renderOpenGraph()`, `renderTwitter()`, `renderJsonLd()` | `Head::render()` |
| `Seo::toArray()` | `Head::toArray()` (different shape) |
| `Seo::title('X')` | `Head::title('X')` |
| `Seo::title('X', exact: true)` | `Head::title('X', exact: true)` |
| `Seo::description('X')` | `Head::description('X')` |
| `Seo::canonical($url)` | `Head::canonical($url)` |
| `Seo::canonicalWithout($params)` | `Head::canonicalWithout($params)` (keeps the other query parameters) |
| `Seo::robots(...)` | `Head::robots(...)` |
| `Seo::noindex()` | `Head::robots('noindex, nofollow')` or `Head::hiddenFromRobots()` (renders `none`) |
| `ThreeOhEight\Seo\RobotsRule` | `Laravel\Head\Enums\RobotsRule` (same cases, plus `NoTranslate`, `IndexIfEmbedded`) |
| `Seo::image($url, $width, $height, $type, $alt)` | `Head::ogImage($url, alt: ..., width: ..., height: ..., type: ...)`; `twitter:image` falls back to it |
| `Seo::meta($name, $content)` | `Head::meta($name, $content)` |
| `Seo::prev($url)`, `Seo::next($url)` | `Head::link('prev', $url)`, `Head::link('next', $url)` |
| `Seo::paginate($paginator)` | `Head::paginate($paginator)` |
| `Seo::alternate($hreflang, $url)`, `Seo::alternates([...])` | `Head::alternates([$hreflang => $url])` |
| `Seo::preconnect($url, crossorigin: true)` | `Head::preconnect($url, crossorigin: true)` |
| `Seo::dnsPrefetch($url)` | `Head::dnsPrefetch($url)` |
| `Seo::og()->type('article')` | `Head::og(type: 'article')` |
| `Seo::og()->title(...)`, `->description(...)` | `Head::og(title: ..., description: ...)` |
| `Seo::og()->locale('en_US')` | `Head::og(locale: 'en_US')` |
| `Seo::og()->alternateLocale('nl_NL')` | `Head::meta('og:locale:alternate', 'nl_NL')` (one value per key) |
| `Seo::twitter()->title(...)`, `->description(...)` | `Head::twitter(title: ..., description: ...)` |
| `Seo::from($model)` | `Head::from($model)` |
| `Seoable::toSeo(Seo $seo)` | `Seoable::toSeo(HeadManager $head)` |
| `WithSeo` component `seo(Seo $seo)` | `seo(HeadManager $head)` |
| `Seo::macro(...)`, `Seo::when(...)`, `Seo::unless(...)` | `Head::macro(...)`, `Head::when(...)`, `Head::unless(...)` |
| `Route::...->seo(title: ..., description: ..., robots: ..., canonical: ...)` | `->withHead(title: ..., description: ..., robots: ..., canonical: ...)` |
| `->seo(noindex: true)` | `->withHead(robots: 'noindex, nofollow')` |
| `->seo(ogType: 'article')` | `->withHead(og: ['type' => 'article'])` |
| `Route::group(['seo' => [...]], ...)` | `Route::withHead(...)->group(...)` |
| `->middleware('seo:noindex,nofollow')` | `->withHead(robots: 'noindex, nofollow')` |
| `Seo::jsonLd('Type')->title('X')->description('Y')->value('k', $v)` | `Head::schema(Schema::make('Type')->name('X')->description('Y')->set('k', $v))` |
| `Seo::jsonLdSeparate('Type')` | `Head::schema(...)` (every schema is its own script tag) |
| `JsonLdBlock::make('Type')` (nested value) | `Schema::make('Type')` |
| `Seo::breadcrumbs(['Home' => '/', 'Page' => null])` | `Head::schema(Schema::breadcrumbs()->items(['Home' => '/', 'Page' => url()->current()]))` (every item needs a URL) |
| `Seo::jsonLd()->softwareApplication($name, $version, $url, $os, $category)` | `Head::schema(Schema::softwareApplication()->name($name)->softwareVersion($version)->url($url)->operatingSystem($os)->applicationCategory($category))` (0.4 emitted `version`; the schema.org property is `softwareVersion`) |
| `Seo::jsonLd()->webApplication($name, $url, $category, $requirements)` | `Head::schema(Schema::webApplication()->name($name)->url($url)->applicationCategory($category)->browserRequirements($requirements))` |
| `Seo::jsonLd()->qaPage($q, $a, $detail, $answerUrl, $date, $author)` | `Head::schema(Schema::qaPage()->question(Schema::question()->name($q)->text($detail)->publishedAt($date)->author($author)->acceptedAnswer(Schema::answer()->text($a)->url($answerUrl)->publishedAt($date)->author($author))))` (`dateCreated` becomes `datePublished`; `answerCount` is derived) |
| `Seo::jsonLd()->faqPage([...])` | Dropped (see Removed); head's `Schema::faq()->questions([$q => $a])` if you still want it |
| `Seo::jsonLd()->article($headline, $description, $published, $modified, $image, $author, ...)` | `Head::schema(Schema::article()->headline(...)->description(...)->publishedAt(...)->modifiedAt(...)->image(...)->author(Schema::person()->name(...)))`; `publisher`, `articleSection`, `keywords`, `inLanguage`, `mainEntityOfPage` via the magic setter, eg `->publisher(Schema::organization()->name('Acme'))` |
| `Seo::jsonLd()->organization($name, $url, $logo, $description)` | `Head::schema(Schema::organization()->name($name)->url($url)->logo($logo)->description($description))` |
| `Seo::jsonLd()->webSite($name, $url, $description, $inLanguage, ...)` | `Head::schema(Schema::webSite()->name($name)->url($url)->description($description)->inLanguage($inLanguage))` |

## 0.4.0 - 2026-08-27

### Added

- Route-level SEO metadata: `Route::get(...)->seo(title: ..., description: ..., robots: ..., canonical: ..., noindex: ..., ogType: ...)` and the `Route::group(['seo' => [...]], ...)` group attribute (plain scalars only). Values are stored in the route action, survive `route:cache`, and form a middle cascade layer resolved field by field: runtime calls beat route metadata, route metadata beats config defaults. Fluent registrar chaining (`Route::seo(...)->group(...)`) is not supported; use the group-attribute syntax.
- `RobotsRule` string-backed enum; `robots()` now accepts a string, a `RobotsRule` case, or a mixed array of both, joined with `, `.
- `title('X', exact: true)` renders the title without the `{separator}{site_name}` formatting, consistently across `<title>`, `og:title` and `twitter:title`.
- `og()->type('article')` overrides `og:type` per page (previously config-only).
- `toArray()` returns all resolved post-cascade values as a structured array (JSON-LD excluded).
- `Seo` is now Conditionable: `Seo::when(...)` / `Seo::unless(...)`.

### Fixed

- `og:title` and `twitter:title` now fall back to the config-level default title, matching `<title>`. Previously a config `title` with no page title made `<title>` and `og:title` diverge.

### Changed

- Apps that registered `when` or `unless` macros on `Seo` will now hit the Conditionable trait methods instead; rename such macros.

## 0.3.0 - 2026-07-11

### Added

- Opt-in MCP server-card discovery: with `seo.server_card.enabled`, the package serves the configured card verbatim at `/.well-known/mcp.json`, `/.well-known/mcp` and `/.well-known/mcp/server-card.json` (SEP-2127's canonical path is still in flux, so every probe path answers identically) with `Cache-Control: max-age=3600`, `X-Content-Type-Options: nosniff` and permissive CORS for GET. Disabled by default; the application owns the exact field shape via config.

## 0.2.0 - 2026-06-15

### Added

- Add `og()->locale()` and `og()->alternateLocale()` to set `og:locale` and `og:locale:alternate` (one tag per entry, deduped), rendered only when set.

## 0.1.0 - 2026-03-20

Initial release.

### Features

- Single `Seo` facade with fluent, chainable API
- `@seo` Blade directive for one-line rendering
- Title construction with configurable separator and site name
- Meta description, robots, canonical (auto or manual), custom meta tags
- Open Graph and Twitter Card with platform-specific overrides via `og()` / `twitter()` proxies
- JSON-LD builder with nested blocks and `@graph` support
- Breadcrumbs helper generating `BreadcrumbList` JSON-LD
- Pagination support (prev/next from `LengthAwarePaginator`)
- `Seoable` interface + `HasSeo` trait for Eloquent models
- `WithSeo` trait for Livewire components (auto-detects Seoable properties)
- Macroable for custom extensions
- Scoped binding (Octane-safe)
- Blade components: `<x-seo::tags />`, `<x-seo::meta />`, `<x-seo::opengraph />`, `<x-seo::twitter />`, `<x-seo::json-ld />`
- Test suite with Pest 4

---

## Migrating from artesaos/seotools

This guide was written for 0.1 to 0.4 and uses the 0.4 `Seo` API. On 0.5, follow it and then apply the "Upgrading from 0.4" table above.

This package replaces `artesaos/seotools` with a simpler, unified API. Below is everything you need to migrate.

### 1. Swap packages

```bash
composer remove artesaos/seotools
composer require three_oh_eight/seo
php artisan vendor:publish --tag=seo-config
```

Delete `config/seotools.php` after migrating its values to `config/seo.php`.

### 2. Config migration

SEOtools splits config across four sections (`meta`, `opengraph`, `twitter`, `json-ld`). This package uses a single flat config.

| `config/seotools.php` | `config/seo.php` |
|------------------------|-------------------|
| `meta.defaults.title` | `title` |
| `meta.defaults.description` | `description` |
| `meta.defaults.separator` | `separator` |
| `meta.defaults.canonical` (false/null) | `auto_canonical` (true/false) |
| `meta.defaults.robots` | `robots` |
| `opengraph.defaults.type` | `og_type` |
| `opengraph.defaults.site_name` | `site_name` |
| `opengraph.defaults.images[0]` | `og_image` |
| `twitter.defaults.card` | `twitter_card` |
| `twitter.defaults.site` | `twitter_site` |

**Dropped:**
- `meta.defaults.keywords`: keywords meta tag is obsolete and not supported
- `meta.defaults.titleBefore`: title is always `{page}{separator}{site}`, use `og()->title()` for overrides
- `meta.webmaster_tags`: use `Seo::meta('google-site-verification', '...')` directly
- Per-provider title/description defaults: one title and description applies everywhere, with per-platform overrides via `og()->title()` / `twitter()->title()`
- `json-ld.defaults`: use `Seo::jsonLd()` in middleware or a service provider for site-wide JSON-LD

### 3. Facade & import changes

```php
// Before (SEOtools)
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\TwitterCard;
use Artesaos\SEOTools\Facades\JsonLd;
use Artesaos\SEOTools\Facades\SEOTools;   // or SEO

// After
use ThreeOhEight\Seo\Facades\Seo;
```

One facade replaces five.

### 4. Method mapping

#### Title & description

```php
// Before
SEO::setTitle('Dashboard');
SEO::setDescription('Overview');
SEOMeta::setTitle('Dashboard', false); // false = don't append site name

// After
Seo::title('Dashboard');
Seo::description('Overview');
Seo::og()->title('Dashboard');  // for title without site name suffix on OG only
```

Note: there is no `$appendDefault` parameter. Titles always follow the `{page}{separator}{site}` pattern. To set a standalone title for OG/Twitter, use the proxy methods.

#### Robots & canonical

```php
// Before
SEOMeta::setRobots('noindex,nofollow');
SEOMeta::setCanonical('https://example.com/page');

// After
Seo::noindex();                              // or Seo::robots('noindex, nofollow')
Seo::canonical('https://example.com/page');  // or rely on auto_canonical
```

#### Open Graph

```php
// Before
OpenGraph::setTitle('OG Title');
OpenGraph::setDescription('OG desc');
OpenGraph::setUrl('https://example.com');
OpenGraph::setType('article');
OpenGraph::addImage('https://example.com/img.jpg');
OpenGraph::addProperty('article:author', 'Christoph');
OpenGraph::setArticle(['published_time' => '2026-01-01']);

// After
Seo::og()->title('OG Title');
Seo::og()->description('OG desc');
Seo::canonical('https://example.com');    // og:url derives from canonical
Seo::image('https://example.com/img.jpg');
Seo::meta('article:author', 'Christoph');
// For article structured data, use JSON-LD instead of OG properties
```

**Key difference:** `og:url` is derived from the canonical URL automatically. No separate `setUrl()` needed.

**Key difference:** `og:type` is set via config (`og_type`), not per-page. If you need per-page types, use a macro or set it via config.

#### Twitter Card

```php
// Before
TwitterCard::setType('summary_large_image');
TwitterCard::setTitle('Twitter Title');
TwitterCard::setSite('@handle');
TwitterCard::setImage('https://example.com/img.jpg');

// After
// card type and site are config values (twitter_card, twitter_site)
Seo::twitter()->title('Twitter Title');
Seo::image('https://example.com/img.jpg');  // shared with OG
```

#### JSON-LD

```php
// Before
JsonLd::setType('Organization');
JsonLd::setTitle('Acme');
JsonLd::setDescription('A company');
JsonLd::addValue('url', 'https://acme.com');

// Before (multiple)
JsonLdMulti::setType('WebPage');
JsonLdMulti::setTitle('Page');
JsonLdMulti::newJsonLd();
JsonLdMulti::setType('Organization');
JsonLdMulti::setTitle('Acme');

// After (single)
Seo::jsonLd('Organization')
    ->title('Acme')
    ->description('A company')
    ->value('url', 'https://acme.com');

// After (multiple: just call jsonLd() again)
Seo::jsonLd('WebPage')->title('Page');
Seo::jsonLd('Organization')->title('Acme');
```

No `JsonLdMulti` needed. Multiple `jsonLd()` calls automatically render as `@graph`.

#### Custom meta tags

```php
// Before
SEOMeta::addMeta('author', 'Christoph');
SEOMeta::addMeta('google-site-verification', 'abc123');

// After
Seo::meta('author', 'Christoph');
Seo::meta('google-site-verification', 'abc123');
```

#### Prev/Next

```php
// Before
SEOMeta::setPrev('https://example.com?page=1');
SEOMeta::setNext('https://example.com?page=3');

// After
Seo::prev('https://example.com?page=1');
Seo::next('https://example.com?page=3');
// Or from a paginator:
Seo::paginate($posts);
```

### 5. Blade templates

```blade
{{-- Before --}}
{!! SEO::generate() !!}
{{-- or --}}
{!! SEOMeta::generate() !!}
{!! OpenGraph::generate() !!}
{!! TwitterCard::generate() !!}
{!! JsonLd::generate() !!}

{{-- After --}}
@seo
{{-- or --}}
<x-seo::tags />
{{-- or individual --}}
<x-seo::meta />
<x-seo::opengraph />
<x-seo::twitter />
<x-seo::json-ld />
```

### 6. Model interface

```php
// Before
use Artesaos\SEOTools\Contracts\SEOFriendly;

class Post extends Model implements SEOFriendly
{
    public function loadSEO(SEOTools $seo)
    {
        $seo->setTitle($this->title);
        $seo->setDescription($this->excerpt);
        $seo->opengraph()->addImage($this->cover_image);
    }
}

// After
use ThreeOhEight\Seo\Contracts\Seoable;
use ThreeOhEight\Seo\Seo;

class Post extends Model implements Seoable
{
    public function toSeo(Seo $seo): void
    {
        $seo->title($this->title)
            ->description($this->excerpt)
            ->image($this->cover_image);
    }
}
```

Or use the `HasSeo` trait if your models follow the `meta_title`/`title` and `meta_description`/`description`/`excerpt` convention:

```php
use ThreeOhEight\Seo\Concerns\HasSeo;
use ThreeOhEight\Seo\Contracts\Seoable;

class Post extends Model implements Seoable
{
    use HasSeo;
}
```

### 7. Controller trait

```php
// Before
use Artesaos\SEOTools\Traits\SEOTools as SEOToolsTrait;

class PostController extends Controller
{
    use SEOToolsTrait;

    public function show(Post $post)
    {
        $this->loadSEO($post);
        return view('posts.show', compact('post'));
    }
}

// After: no trait needed
class PostController extends Controller
{
    public function show(Post $post)
    {
        Seo::from($post);
        return view('posts.show', compact('post'));
    }
}
```

### 8. Macros

```php
// Before
SEOTools::macro('webPage', function ($title, $desc) {
    SEOMeta::setTitle($title)->setDescription($desc);
    OpenGraph::setTitle($title)->setDescription($desc)->setUrl(url()->current());
});

// After
Seo::macro('webPage', function (string $title, string $desc) {
    return $this->title($title)->description($desc);
    // OG title/description cascade automatically, canonical handles og:url
});
```

### 9. Features not carried over

| SEOtools feature | Status | Alternative |
|-----------------|--------|-------------|
| Keywords meta tag | Dropped | Obsolete, ignored by all major search engines |
| `titleBefore` config | Dropped | Title is always `{page}{separator}{site}` |
| Webmaster verification tags | Dropped | Use `Seo::meta()` in a service provider |
| `addAlternateLanguage()` | Dropped | Use `Seo::meta()` or a custom Blade partial |
| `OpenGraph::setArticle()` | Dropped | Use JSON-LD `Article` type instead |
| `OpenGraph::addVideo/Audio()` | Dropped | Use `Seo::meta()` for `og:video`/`og:audio` |
| `generate($minify)` | Dropped | Output is already compact, no minification toggle |
| Per-provider config defaults | Simplified | One set of defaults, override per-platform via proxies |
| Inertia support | Not included | Set SEO data server-side and pass to Inertia props |

### 10. Search-and-replace checklist

Run these across your codebase to catch most references:

```
SEOMeta::          -> Seo::
SEO::setTitle(     -> Seo::title(
SEO::setDescription( -> Seo::description(
SEO::setCanonical( -> Seo::canonical(
SEO::addImages(    -> Seo::image(
SEO::generate()    -> (use @seo directive)
OpenGraph::setTitle(    -> Seo::og()->title(
OpenGraph::setDescription( -> Seo::og()->description(
OpenGraph::addImage(    -> Seo::image(
TwitterCard::setTitle(  -> Seo::twitter()->title(
TwitterCard::setSite(   -> (move to config twitter_site)
JsonLd::setType(        -> Seo::jsonLd(
JsonLd::setTitle(       -> (chain ->title() on jsonLd())
JsonLd::addValue(       -> (chain ->value() on jsonLd())
SEOMeta::setRobots('noindex -> Seo::noindex()
SEOMeta::setCanonical(  -> Seo::canonical(
SEOMeta::setPrev(       -> Seo::prev(
SEOMeta::setNext(       -> Seo::next(
{!! SEO::generate() !!} -> @seo
loadSEO(                -> Seo::from(
SEOFriendly             -> Seoable
```

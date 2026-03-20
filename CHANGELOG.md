# Changelog

## 1.0.0 — 2026-03-20

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
- 116 tests with Pest 4

---

## Migrating from artesaos/seotools

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
- `meta.defaults.keywords` — keywords meta tag is obsolete and not supported
- `meta.defaults.titleBefore` — title is always `{page}{separator}{site}`, use `og()->title()` for overrides
- `meta.webmaster_tags` — use `Seo::meta('google-site-verification', '...')` directly
- Per-provider title/description defaults — one title and description applies everywhere, with per-platform overrides via `og()->title()` / `twitter()->title()`
- `json-ld.defaults` — use `Seo::jsonLd()` in middleware or a service provider for site-wide JSON-LD

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

// After (multiple — just call jsonLd() again)
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

// After — no trait needed
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

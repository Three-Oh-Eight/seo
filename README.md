# three_oh_eight/seo

Lean, Laravel-native SEO tag management. One facade, one config file, zero bloat.

Built for Laravel 12+ and PHP 8.5+. Octane-safe (scoped binding).

## Installation

```bash
composer require three_oh_eight/seo
```

The service provider and `Seo` facade are auto-discovered.

Publish the config:

```bash
php artisan vendor:publish --tag=seo-config
```

## Quick start

Set SEO data anywhere (controller, middleware, Livewire component):

```php
use ThreeOhEight\Seo\Facades\Seo;

Seo::title('Dashboard')
    ->description('Your account overview')
    ->image('https://example.com/og.jpg');
```

Render in your layout:

```blade
<head>
    @seo
</head>
```

That's it. The `@seo` directive renders `<title>`, meta description, canonical, robots, Open Graph, Twitter Card, and JSON-LD tags.

## Configuration

All defaults live in `config/seo.php`:

```php
return [
    'site_name'      => env('APP_NAME', 'My App'),
    'separator'      => ' - ',
    'title'          => null,          // default page title (null = site name only)
    'description'    => null,          // default meta description
    'auto_canonical' => true,          // auto-generate from url()->current()
    'robots'         => null,          // null = don't render robots meta
    'og_type'        => 'website',
    'og_image'       => null,          // fallback OG image
    'twitter_card'   => 'summary_large_image',
    'twitter_image'  => null,          // fallback Twitter image
    'twitter_site'   => null,          // @handle
];
```

## API reference

### Basic tags

```php
Seo::title('About Us');                     // <title>About Us - Site Name</title>
Seo::description('Learn about us');         // <meta name="description" ...>
Seo::canonical('https://example.com/about');// <link rel="canonical" ...>
Seo::image('https://example.com/og.jpg');   // og:image + twitter:image
Seo::robots('noindex, follow');             // <meta name="robots" ...>
Seo::noindex();                             // shortcut: "noindex, nofollow"
Seo::meta('author', 'Christoph');           // arbitrary <meta> tags
```

### Pagination

```php
Seo::prev('https://example.com?page=1');
Seo::next('https://example.com?page=3');

// Or from a paginator:
Seo::paginate($posts);  // auto-sets prev/next from LengthAwarePaginator
```

### Open Graph & Twitter overrides

Override platform-specific titles/descriptions when they should differ from the `<title>` tag:

```php
Seo::og()->title('Custom OG title');
Seo::og()->description('Custom OG description');

Seo::twitter()->title('Custom Twitter title');
Seo::twitter()->description('Custom Twitter description');
```

Both proxies return the `Seo` instance, so you can chain:

```php
Seo::title('Page')
    ->og()->title('OG override')
    ->twitter()->title('Twitter override')
    ->description('Shared description');
```

### JSON-LD

```php
Seo::jsonLd('Organization')
    ->title('Acme Corp')
    ->description('We make things')
    ->value('url', 'https://acme.com')
    ->value('logo', 'https://acme.com/logo.png');
```

Multiple blocks render as a `@graph` array:

```php
Seo::jsonLd('WebSite')->title('Acme')->value('url', 'https://acme.com');
Seo::jsonLd('Organization')->title('Acme Corp');
// Outputs: {"@context":"https://schema.org","@graph":[...]}
```

Nested blocks:

```php
$address = JsonLdBlock::make('PostalAddress')->value('addressLocality', 'Rotterdam');
Seo::jsonLd('Organization')->title('Acme')->value('address', $address);
```

### Breadcrumbs

```php
Seo::breadcrumbs([
    'Home'     => '/',
    'Products' => '/products',
    'Widget'   => null,  // null = current page (no URL in output)
]);
```

Renders a `BreadcrumbList` JSON-LD block with auto-incrementing positions.

### Seoable models

Implement the `Seoable` interface on your models:

```php
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

Then in a controller:

```php
Seo::from($post);
```

Or use the `HasSeo` trait for convention-based mapping (pulls from `meta_title`/`title` and `meta_description`/`description`/`excerpt`):

```php
use ThreeOhEight\Seo\Concerns\HasSeo;
use ThreeOhEight\Seo\Contracts\Seoable;

class Post extends Model implements Seoable
{
    use HasSeo;
}
```

### Livewire integration

Add `WithSeo` to your Livewire component:

```php
use ThreeOhEight\Seo\Concerns\WithSeo;

class ShowPost extends Component
{
    use WithSeo;

    public Post $post; // auto-detected if Post implements Seoable

    // Or define a custom seo() method:
    public function seo(Seo $seo): void
    {
        $seo->title($this->post->title)
            ->description($this->post->excerpt);
    }
}
```

The trait hooks into `rendering()` and applies SEO data automatically. If a `seo()` method exists, it takes priority over auto-detection of Seoable properties.

### Macros

The `Seo` class uses Laravel's `Macroable` trait:

```php
Seo::macro('article', function (string $author, string $published) {
    return $this->meta('article:author', $author)
                ->meta('article:published_time', $published);
});

Seo::article('Christoph', '2026-01-01');
```

## Rendering

### Blade directive (recommended)

```blade
<head>
    @seo
</head>
```

### Blade components

```blade
<x-seo::tags />          {{-- all sections --}}
<x-seo::meta />          {{-- title, description, canonical, robots, prev/next, custom meta --}}
<x-seo::opengraph />     {{-- og:* tags --}}
<x-seo::twitter />       {{-- twitter:* tags --}}
<x-seo::json-ld />       {{-- JSON-LD script --}}
```

### Programmatic

```php
$html = Seo::render();          // SeoOutput (Htmlable + Stringable)
$html = Seo::renderMeta();      // meta section only
$html = Seo::renderOpenGraph();
$html = Seo::renderTwitter();
$html = Seo::renderJsonLd();
```

## Fallback cascade

Tags resolve in this order:

| Tag | Resolution |
|-----|-----------|
| `<title>` | `title()` > config `title` > site name only |
| `meta description` | `description()` > config `description` > omitted |
| `canonical` | `canonical()` > `url()->current()` (if `auto_canonical`) > omitted |
| `robots` | `robots()`/`noindex()` > config `robots` > omitted |
| `og:title` | `og()->title()` > formatted page title > site name |
| `og:description` | `og()->description()` > `description()` > config `description` > omitted |
| `og:image` | `image()` > config `og_image` > omitted |
| `twitter:title` | `twitter()->title()` > formatted page title > site name |
| `twitter:description` | `twitter()->description()` > `description()` > config `description` > omitted |
| `twitter:image` | `image()` > config `twitter_image` > omitted |

## Title format

Titles are formatted as `{page title}{separator}{site name}`:

- `Seo::title('Dashboard')` renders `Dashboard - My App`
- No page title renders `My App`

OG and Twitter titles use the same formatted title unless overridden via their proxies.

## Testing

```bash
composer test
# or
./vendor/bin/pest
```

## License

MIT

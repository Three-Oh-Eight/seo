# three_oh_eight/seo

A thin add-on for [laravel/head](https://github.com/laravel/head). Head renders the page head (title, meta, canonical, robots, Open Graph, Twitter, icons, feeds, JSON-LD) and resolves it as defaults, then route `withHead()` metadata, then runtime `Head::` calls. This package only adds what head leaves out:

- **Config defaults**: `config/seo.php` (site name, title separator, description, robots, og/twitter defaults) feeds head's defaults layer.
- **Seoable models**: `Seoable` contract, `HasSeo` trait and `Head::from($model)`.
- **Livewire**: the `WithSeo` trait applies head data before every render.
- **`Head::canonicalWithout()`**: a canonical that keeps the query string minus tracking parameters.
- **`og:url`**: rendered from the resolved canonical URL.
- **Schema types**: `SoftwareApplication`, `WebApplication` and `QAPage` builders on head's `Schema` factory.
- **MCP server card**: opt-in `/.well-known/mcp*` discovery routes.

Requires PHP 8.5+, Laravel 13.17+ and laravel/head ^0.2. Octane safe: head keeps per-request state in a scoped binding, and this package registers everything once at boot.

## Installation

```bash
composer require three_oh_eight/seo
php artisan vendor:publish --tag=seo-config
```

Both service providers are auto-discovered. Render the head in your layout with head's directive:

```blade
<head>
    @head
</head>
```

## Configuration

```php
// config/seo.php
return [
    'site_name' => env('APP_NAME', 'Laravel'),
    'separator' => ' - ',
    'title' => null,             // fallback page title; null renders the site name alone
    'description' => 'A Laravel application.',
    'auto_canonical' => true,    // canonical from the current URL, query string dropped
    'robots' => null,
    'og_type' => 'website',
    'og_image' => '/android-chrome-512x512.png',
    'twitter_card' => 'summary_large_image',
    'twitter_image' => '/android-chrome-512x512.png', // only rendered when it differs from og_image
    'twitter_site' => null,
    'og_url' => true,            // og:url from the canonical URL

    'canonical_strip' => [],     // fnmatch patterns for Head::canonicalWithout(), eg 'utm_*'

    'server_card' => ['enabled' => false, 'card' => []],
];
```

The values land in `Head::defaults()`, so route metadata and runtime calls override them. Page titles render as `{page}{separator}{site_name}`; `Head::title('X', exact: true)` skips the suffix. Without a page title the default is `{title}{separator}{site_name}`, or the site name alone when `title` is null.

## Usage

Everything page-specific is plain head API:

```php
use Laravel\Head\Facades\Head;

Route::get('/pricing', PricingController::class)
    ->withHead(title: 'Pricing', description: 'Plans and pricing');

Head::title('Dashboard')->description('Your overview');
Head::robots('noindex, nofollow');
Head::ogImage('https://example.com/og.jpg', alt: 'Dashboard');
```

See the [laravel/head documentation](https://github.com/laravel/head) for the full API.

### Seoable models

```php
use Laravel\Head\HeadManager;
use ThreeOhEight\Seo\Contracts\Seoable;

class Post extends Model implements Seoable
{
    public function toSeo(HeadManager $head): void
    {
        $head->title($this->title)
            ->description($this->excerpt)
            ->ogImage($this->cover_url);
    }
}

Head::from($post); // returns the head manager, so you can keep chaining
```

Or use the `HasSeo` trait, which maps `meta_title` or `title` and `meta_description`, `description` or `excerpt`, skipping empty values:

```php
use ThreeOhEight\Seo\Concerns\HasSeo;
use ThreeOhEight\Seo\Contracts\Seoable;

class Post extends Model implements Seoable
{
    use HasSeo;
}
```

### Livewire

```php
use Laravel\Head\HeadManager;
use ThreeOhEight\Seo\Concerns\WithSeo;

class ShowPost extends Component
{
    use WithSeo;

    public Post $post; // every public Seoable property is applied

    // Optional; when present it replaces the property detection.
    public function seo(HeadManager $head): void
    {
        $head->title($this->post->title);
    }
}
```

### Canonical without tracking parameters

Head's automatic canonical drops the whole query string. On pages where some parameters matter (filters, pagination), keep them and strip the rest:

```php
Head::canonicalWithout();                 // strips the seo.canonical_strip patterns
Head::canonicalWithout(['ref', 'sort']);  // plus these, fnmatch syntax
```

### og:url

Head only renders `og:url` when you set it. This package renders it from the resolved canonical URL on every page that has Open Graph tags, so it follows `Head::canonical()`, route canonicals and `Head::canonicalWithout()`.

```php
Head::og(url: 'https://example.com/share');                  // override at runtime
Route::get(...)->withHead(og: ['url' => 'https://...']);      // or per route
Route::get(...)->withHead(extensions: ['ogUrl' => false]);    // turn it off for a route
```

Set `seo.og_url` to `false` to turn it off everywhere. The tag renders after head's own tags, at the end of `@head`.

## Schema types

Head ships builders for Article, BlogPosting, Event, Product, Offer, Brand, BreadcrumbList, FAQPage, Organization, Person, WebPage and WebSite. This package registers five more on the same `Schema` factory:

| Method | `@type` | Google rich result |
|---|---|---|
| `Schema::softwareApplication()` | SoftwareApplication | Software app |
| `Schema::webApplication()` | WebApplication | Software app |
| `Schema::qaPage()` | QAPage | Q&A |
| `Schema::question()` | Question | (QAPage mainEntity) |
| `Schema::answer()` | Answer | (accepted or suggested answer) |

Every setter takes nullable input and leaves null or empty values out, so model attributes can go in directly. Head's validator throws on empty values outside production, which this avoids. Properties without a typed method go through head's magic setter (`->datePublished('2026-01-01')`) or `->set('key', $value)`.

### SoftwareApplication and WebApplication

Google needs `name`, an offer with a price, and an `aggregateRating` or `review`; `applicationCategory` and `operatingSystem` are recommended.

```php
use Laravel\Head\Facades\Head;
use Laravel\Head\Facades\Schema;

Head::schema(Schema::softwareApplication()
    ->name('Payflo')
    ->description($app->description)
    ->url('https://payflo.eu')
    ->applicationCategory('BusinessApplication')
    ->operatingSystem('Windows, macOS')
    ->softwareVersion('2.1')
    ->price(0, 'EUR')                          // or ->offers(Schema::offer()->...)
    ->aggregateRating(4.6, count: 120, best: 5)
    ->review('Jane Doe', 5, body: 'Does the job.', publishedAt: now()));
```

`webApplication()` has the same methods plus `browserRequirements()`.

### QAPage

For a page about one question and its answers (community or support Q&A, not a FAQ the site writes itself):

```php
Head::schema(Schema::qaPage()->question(
    Schema::question()
        ->name($question->title)
        ->text($question->body)
        ->author($question->user->name)        // string, Person or Organization
        ->publishedAt($question->created_at)
        ->upvotes($question->votes)
        ->acceptedAnswer(Schema::answer()
            ->text($accepted->body)
            ->author($accepted->user->name)
            ->url(route('questions.show', $question).'#answer-'.$accepted->id)
            ->upvotes($accepted->votes)
            ->publishedAt($accepted->created_at))
        ->suggestedAnswer(...$others->map(fn ($answer) => Schema::answer()->text($answer->body)))
));
```

`answerCount` defaults to the number of accepted plus suggested answers; call `->answerCount($n)` when the page shows more answers than the markup lists. Head renders every schema as its own `<script>` tag, which is what Google expects for QAPage.

## MCP server card

If the application hosts an MCP server, the package can serve its discovery document. Enable it and provide the card verbatim (the shape follows SEP-2127, still a draft; the package never rewrites your payload):

```php
// config/seo.php
'server_card' => [
    'enabled' => true,
    'card' => [
        'protocolVersion' => '2025-06-18',
        'serverInfo' => ['name' => 'my-app', 'version' => '1.0.0'],
        'transport' => ['type' => 'streamable-http', 'endpoint' => 'https://example.com/mcp'],
        'capabilities' => ['tools' => true],
        'authentication' => ['required' => true, 'method' => 'oauth2'],
    ],
],
```

The card is served at `/.well-known/mcp.json`, `/.well-known/mcp` and `/.well-known/mcp/server-card.json` (identical payload; the canonical path has moved between spec revisions) with hourly caching, `nosniff` and permissive CORS. Disabled by default: no routes are registered without the flag.

## Upgrading from 0.4

0.5 is a rewrite on top of laravel/head. See the "Upgrading from 0.4" table in [CHANGELOG.md](CHANGELOG.md).

## Testing

```bash
./vendor/bin/pest
```

## License

MIT

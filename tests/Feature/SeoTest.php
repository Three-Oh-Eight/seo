<?php

use Illuminate\Pagination\LengthAwarePaginator;
use ThreeOhEight\Seo\Contracts\Seoable;
use ThreeOhEight\Seo\Seo;
use ThreeOhEight\Seo\SeoOutput;

// --- Title rendering ---

it('renders page title with separator and site name', function () {
    $seo = $this->makeSeo();
    $seo->title('Dashboard');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('<title>Dashboard - TestSite</title>');
});

it('renders site name only when no page title', function () {
    $seo = $this->makeSeo();

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('<title>TestSite</title>');
});

it('renders default title from config', function () {
    $seo = $this->makeSeo(['title' => 'Default Page']);

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('<title>Default Page - TestSite</title>');
});

it('escapes html in titles', function () {
    $seo = $this->makeSeo();
    $seo->title('Tom & Jerry <script>');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('Tom &amp; Jerry &lt;script&gt;')
        ->and($html)->not->toContain('<script>');
});

// --- Description ---

it('renders meta description', function () {
    $seo = $this->makeSeo();
    $seo->description('A great page');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('<meta name="description" content="A great page">');
});

it('falls back to config default description', function () {
    $seo = $this->makeSeo(['description' => 'Default desc']);

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('content="Default desc"');
});

it('omits description when null everywhere', function () {
    $seo = $this->makeSeo();

    $html = $seo->renderMeta()->toHtml();

    expect($html)->not->toContain('name="description"');
});

it('escapes html in description', function () {
    $seo = $this->makeSeo();
    $seo->description('Use <em>caution</em> & care');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('content="Use &lt;em&gt;caution&lt;/em&gt; &amp; care"');
});

// --- Robots ---

it('renders custom robots string', function () {
    $seo = $this->makeSeo();
    $seo->robots('noindex, follow');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('<meta name="robots" content="noindex, follow">');
});

it('renders noindex nofollow via noindex shortcut', function () {
    $seo = $this->makeSeo();
    $seo->noindex();

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('<meta name="robots" content="noindex, nofollow">');
});

it('falls back to config default robots', function () {
    $seo = $this->makeSeo(['robots' => 'noarchive']);

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('content="noarchive"');
});

it('omits robots when null', function () {
    $seo = $this->makeSeo();

    $html = $seo->renderMeta()->toHtml();

    expect($html)->not->toContain('name="robots"');
});

// --- Canonical ---

it('renders manual canonical url', function () {
    $seo = $this->makeSeo();
    $seo->canonical('https://example.com/page');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('<link rel="canonical" href="https://example.com/page">');
});

it('renders auto canonical from current url', function () {
    $seo = $this->makeSeo(['auto_canonical' => true]);

    $this->get('/test-page');
    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('<link rel="canonical"');
});

it('omits canonical when disabled and no manual', function () {
    $seo = $this->makeSeo(['auto_canonical' => false]);

    $html = $seo->renderMeta()->toHtml();

    expect($html)->not->toContain('rel="canonical"');
});

it('manual canonical overrides auto', function () {
    $seo = $this->makeSeo(['auto_canonical' => true]);
    $seo->canonical('https://example.com/override');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('href="https://example.com/override"');
});

// --- Image ---

it('renders explicit image in og and twitter', function () {
    $seo = $this->makeSeo();
    $seo->image('https://example.com/image.jpg');

    $ogHtml = $seo->renderOpenGraph()->toHtml();
    $twHtml = $seo->renderTwitter()->toHtml();

    expect($ogHtml)->toContain('content="https://example.com/image.jpg"')
        ->and($twHtml)->toContain('content="https://example.com/image.jpg"');
});

it('falls back to og_image config', function () {
    $seo = $this->makeSeo(['og_image' => '/default-og.png']);

    $html = $seo->renderOpenGraph()->toHtml();

    expect($html)->toContain('content="/default-og.png"');
});

it('falls back to twitter_image config', function () {
    $seo = $this->makeSeo(['twitter_image' => '/default-tw.png']);

    $html = $seo->renderTwitter()->toHtml();

    expect($html)->toContain('content="/default-tw.png"');
});

// --- Custom meta tags ---

it('renders a single custom meta tag', function () {
    $seo = $this->makeSeo();
    $seo->meta('author', 'Christoph');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('<meta name="author" content="Christoph">');
});

it('renders multiple meta tags in order', function () {
    $seo = $this->makeSeo();
    $seo->meta('author', 'Christoph')->meta('generator', 'Laravel');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('name="author"')
        ->and($html)->toContain('name="generator"');
});

it('escapes meta name and content', function () {
    $seo = $this->makeSeo();
    $seo->meta('x-"test"', 'value & <more>');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('name="x-&quot;test&quot;"')
        ->and($html)->toContain('content="value &amp; &lt;more&gt;"');
});

// --- Prev/Next ---

it('renders prev link', function () {
    $seo = $this->makeSeo();
    $seo->prev('https://example.com/page/1');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('<link rel="prev" href="https://example.com/page/1">');
});

it('renders next link', function () {
    $seo = $this->makeSeo();
    $seo->next('https://example.com/page/3');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('<link rel="next" href="https://example.com/page/3">');
});

it('renders both prev and next', function () {
    $seo = $this->makeSeo();
    $seo->prev('https://example.com/page/1')->next('https://example.com/page/3');

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('rel="prev"')
        ->and($html)->toContain('rel="next"');
});

// --- Paginate ---

it('sets prev from paginator on page 2+', function () {
    $paginator = Mockery::mock(LengthAwarePaginator::class);
    $paginator->shouldReceive('currentPage')->andReturn(2);
    $paginator->shouldReceive('previousPageUrl')->andReturn('https://example.com/page/1');
    $paginator->shouldReceive('hasMorePages')->andReturn(false);

    $seo = $this->makeSeo();
    $seo->paginate($paginator);

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('rel="prev" href="https://example.com/page/1"')
        ->and($html)->not->toContain('rel="next"');
});

it('sets next when paginator has more pages', function () {
    $paginator = Mockery::mock(LengthAwarePaginator::class);
    $paginator->shouldReceive('currentPage')->andReturn(1);
    $paginator->shouldReceive('hasMorePages')->andReturn(true);
    $paginator->shouldReceive('nextPageUrl')->andReturn('https://example.com/page/2');

    $seo = $this->makeSeo();
    $seo->paginate($paginator);

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('rel="next" href="https://example.com/page/2"')
        ->and($html)->not->toContain('rel="prev"');
});

it('sets no prev on page 1', function () {
    $paginator = Mockery::mock(LengthAwarePaginator::class);
    $paginator->shouldReceive('currentPage')->andReturn(1);
    $paginator->shouldReceive('hasMorePages')->andReturn(false);

    $seo = $this->makeSeo();
    $seo->paginate($paginator);

    $html = $seo->renderMeta()->toHtml();

    expect($html)->not->toContain('rel="prev"')
        ->and($html)->not->toContain('rel="next"');
});

it('sets no next on last page', function () {
    $paginator = Mockery::mock(LengthAwarePaginator::class);
    $paginator->shouldReceive('currentPage')->andReturn(5);
    $paginator->shouldReceive('previousPageUrl')->andReturn('https://example.com/page/4');
    $paginator->shouldReceive('hasMorePages')->andReturn(false);

    $seo = $this->makeSeo();
    $seo->paginate($paginator);

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('rel="prev"')
        ->and($html)->not->toContain('rel="next"');
});

// --- OpenGraph rendering ---

it('always renders og:type and og:site_name', function () {
    $seo = $this->makeSeo();

    $html = $seo->renderOpenGraph()->toHtml();

    expect($html)->toContain('property="og:type" content="website"')
        ->and($html)->toContain('property="og:site_name" content="TestSite"');
});

it('renders og:title from formatted page title', function () {
    $seo = $this->makeSeo();
    $seo->title('Dashboard');

    $html = $seo->renderOpenGraph()->toHtml();

    expect($html)->toContain('property="og:title" content="Dashboard - TestSite"');
});

it('renders og:title as site name alone when no page title', function () {
    $seo = $this->makeSeo();

    $html = $seo->renderOpenGraph()->toHtml();

    expect($html)->toContain('property="og:title" content="TestSite"');
});

it('renders og:title override via og proxy', function () {
    $seo = $this->makeSeo();
    $seo->title('Dashboard');
    $seo->og()->title('Custom OG Title');

    $html = $seo->renderOpenGraph()->toHtml();

    expect($html)->toContain('property="og:title" content="Custom OG Title"');
});

it('cascades og:description from ogDescription to description to default', function () {
    // Explicit ogDescription
    $seo = $this->makeSeo(['description' => 'Default']);
    $seo->description('Page desc');
    $seo->og()->description('OG desc');
    expect($seo->renderOpenGraph()->toHtml())->toContain('content="OG desc"');

    // Falls back to page description
    $seo2 = $this->makeSeo(['description' => 'Default']);
    $seo2->description('Page desc');
    expect($seo2->renderOpenGraph()->toHtml())->toContain('content="Page desc"');

    // Falls back to config default
    $seo3 = $this->makeSeo(['description' => 'Default']);
    expect($seo3->renderOpenGraph()->toHtml())->toContain('content="Default"');
});

it('renders og:url from canonical', function () {
    $seo = $this->makeSeo();
    $seo->canonical('https://example.com/page');

    $html = $seo->renderOpenGraph()->toHtml();

    expect($html)->toContain('property="og:url" content="https://example.com/page"');
});

it('renders og:image', function () {
    $seo = $this->makeSeo();
    $seo->image('https://example.com/img.jpg');

    $html = $seo->renderOpenGraph()->toHtml();

    expect($html)->toContain('property="og:image" content="https://example.com/img.jpg"');
});

it('omits og:image when no image set', function () {
    $seo = $this->makeSeo();

    $html = $seo->renderOpenGraph()->toHtml();

    expect($html)->not->toContain('og:image');
});

it('omits og:description when all null', function () {
    $seo = $this->makeSeo();

    $html = $seo->renderOpenGraph()->toHtml();

    expect($html)->not->toContain('og:description');
});

// --- Twitter rendering ---

it('always renders twitter:card', function () {
    $seo = $this->makeSeo();

    $html = $seo->renderTwitter()->toHtml();

    expect($html)->toContain('name="twitter:card" content="summary_large_image"');
});

it('renders twitter:title from formatted page title', function () {
    $seo = $this->makeSeo();
    $seo->title('Dashboard');

    $html = $seo->renderTwitter()->toHtml();

    expect($html)->toContain('name="twitter:title" content="Dashboard - TestSite"');
});

it('renders twitter:title override via twitter proxy', function () {
    $seo = $this->makeSeo();
    $seo->title('Dashboard');
    $seo->twitter()->title('Custom Twitter Title');

    $html = $seo->renderTwitter()->toHtml();

    expect($html)->toContain('name="twitter:title" content="Custom Twitter Title"');
});

it('cascades twitter:description', function () {
    $seo = $this->makeSeo(['description' => 'Default']);
    $seo->description('Page desc');
    $seo->twitter()->description('Twitter desc');
    expect($seo->renderTwitter()->toHtml())->toContain('content="Twitter desc"');

    $seo2 = $this->makeSeo(['description' => 'Default']);
    $seo2->description('Page desc');
    expect($seo2->renderTwitter()->toHtml())->toContain('content="Page desc"');

    $seo3 = $this->makeSeo(['description' => 'Default']);
    expect($seo3->renderTwitter()->toHtml())->toContain('content="Default"');
});

it('renders twitter:image', function () {
    $seo = $this->makeSeo();
    $seo->image('https://example.com/img.jpg');

    $html = $seo->renderTwitter()->toHtml();

    expect($html)->toContain('name="twitter:image" content="https://example.com/img.jpg"');
});

it('renders twitter:site when configured', function () {
    $seo = $this->makeSeo(['twitter_site' => '@testsite']);

    $html = $seo->renderTwitter()->toHtml();

    expect($html)->toContain('name="twitter:site" content="@testsite"');
});

it('omits twitter:site when not configured', function () {
    $seo = $this->makeSeo();

    $html = $seo->renderTwitter()->toHtml();

    expect($html)->not->toContain('twitter:site');
});

it('omits twitter:description when all null', function () {
    $seo = $this->makeSeo();

    $html = $seo->renderTwitter()->toHtml();

    expect($html)->not->toContain('twitter:description');
});

it('omits twitter:image when no image set', function () {
    $seo = $this->makeSeo();

    $html = $seo->renderTwitter()->toHtml();

    expect($html)->not->toContain('twitter:image');
});

// --- JSON-LD rendering ---

it('renders empty output when no jsonld blocks', function () {
    $seo = $this->makeSeo();

    $html = $seo->renderJsonLd()->toHtml();

    expect($html)->toBe('');
});

it('renders single jsonld block', function () {
    $seo = $this->makeSeo();
    $seo->jsonLd('Organization')->title('Acme');

    $html = $seo->renderJsonLd()->toHtml();

    expect($html)->toContain('application/ld+json')
        ->and($html)->toContain('"@type":"Organization"')
        ->and($html)->toContain('"name":"Acme"');
});

it('renders multiple jsonld blocks as graph', function () {
    $seo = $this->makeSeo();
    $seo->jsonLd('Organization')->title('Acme');
    $seo->jsonLd('WebSite')->title('Acme Site');

    $html = $seo->renderJsonLd()->toHtml();

    expect($html)->toContain('"@graph"');
});

// --- Breadcrumbs ---

it('renders breadcrumb list with itemListElement', function () {
    $seo = $this->makeSeo();
    $seo->breadcrumbs(['Home' => '/', 'Products' => '/products', 'Widget' => null]);

    $html = $seo->renderJsonLd()->toHtml();
    $json = str_replace(['<script type="application/ld+json">', '</script>'], '', $html);
    $data = json_decode($json, true);

    expect($data['@type'])->toBe('BreadcrumbList')
        ->and($data['itemListElement'])->toHaveCount(3);
});

it('auto-increments positions in breadcrumbs', function () {
    $seo = $this->makeSeo();
    $seo->breadcrumbs(['Home' => '/', 'Products' => '/products']);

    $html = $seo->renderJsonLd()->toHtml();
    $json = str_replace(['<script type="application/ld+json">', '</script>'], '', $html);
    $data = json_decode($json, true);

    expect($data['itemListElement'][0]['position'])->toBe(1)
        ->and($data['itemListElement'][1]['position'])->toBe(2);
});

it('omits item property when url is null in breadcrumbs', function () {
    $seo = $this->makeSeo();
    $seo->breadcrumbs(['Current Page' => null]);

    $html = $seo->renderJsonLd()->toHtml();
    $json = str_replace(['<script type="application/ld+json">', '</script>'], '', $html);
    $data = json_decode($json, true);

    expect($data['itemListElement'][0])->not->toHaveKey('item')
        ->and($data['itemListElement'][0]['name'])->toBe('Current Page');
});

// --- Fluent API ---

it('supports method chaining', function () {
    $seo = $this->makeSeo();

    $result = $seo->title('Page')
        ->description('Desc')
        ->noindex()
        ->image('https://example.com/img.jpg');

    expect($result)->toBeInstanceOf(Seo::class);

    $html = $seo->render()->toHtml();

    expect($html)->toContain('<title>Page - TestSite</title>')
        ->and($html)->toContain('content="Desc"')
        ->and($html)->toContain('noindex, nofollow')
        ->and($html)->toContain('https://example.com/img.jpg');
});

it('supports og/twitter proxy chaining back to seo', function () {
    $seo = $this->makeSeo();

    $result = $seo->og()->title('OG')
        ->twitter()->title('TW')
        ->title('Page Title');

    expect($result)->toBeInstanceOf(Seo::class);
});

// --- from(Seoable) ---

it('calls toSeo on the seoable', function () {
    $seoable = new class implements Seoable
    {
        public function toSeo(Seo $seo): void
        {
            $seo->title('From Seoable');
        }
    };

    $seo = $this->makeSeo();
    $seo->from($seoable);

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('<title>From Seoable - TestSite</title>');
});

it('returns self from from() for chaining', function () {
    $seoable = new class implements Seoable
    {
        public function toSeo(Seo $seo): void {}
    };

    $seo = $this->makeSeo();
    $result = $seo->from($seoable);

    expect($result)->toBeInstanceOf(Seo::class);
});

// --- Macroable ---

it('supports registering and calling macros', function () {
    Seo::macro('articleMeta', function (string $author, string $publishedAt) {
        /** @var Seo $this */
        $this->meta('article:author', $author);
        $this->meta('article:published_time', $publishedAt);

        return $this;
    });

    $seo = $this->makeSeo();
    $result = $seo->articleMeta('Christoph', '2026-01-01');

    expect($result)->toBeInstanceOf(Seo::class);

    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('content="Christoph"')
        ->and($html)->toContain('content="2026-01-01"');
});

// --- Full render() ---

it('combines all sections in render output', function () {
    $seo = $this->makeSeo(['twitter_site' => '@test']);
    $seo->title('Dashboard')->description('Overview');
    $seo->jsonLd('WebPage')->title('Dashboard');

    $output = $seo->render();

    expect($output)->toBeInstanceOf(SeoOutput::class);

    $html = $output->toHtml();

    expect($html)->toContain('<title>')
        ->and($html)->toContain('og:title')
        ->and($html)->toContain('twitter:title')
        ->and($html)->toContain('application/ld+json');
});

it('omits empty json-ld section from render', function () {
    $seo = $this->makeSeo();
    $seo->title('Page');

    $html = $seo->render()->toHtml();

    expect($html)->not->toContain('application/ld+json');
});

it('returns SeoOutput from render', function () {
    $seo = $this->makeSeo();

    expect($seo->render())->toBeInstanceOf(SeoOutput::class);
});

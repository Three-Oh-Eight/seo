<?php

namespace ThreeOhEight\Seo;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Route;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use ThreeOhEight\Seo\Contracts\Seoable;
use ThreeOhEight\Seo\JsonLd\JsonLdBlock;
use ThreeOhEight\Seo\JsonLd\JsonLdHelper;
use ThreeOhEight\Seo\Routing\RouteSeo;

class Seo
{
    use Conditionable;
    use Macroable {
        __call as macroCall;
    }

    /** @var array<string, string>|null */
    private ?array $routeSeo = null;

    public function __construct(
        private SeoData $data,
        private readonly SeoDefaults $defaults,
    ) {}

    public function title(string $title, bool $exact = false): self
    {
        $this->data->title = $title;
        $this->data->titleExact = $exact;

        return $this;
    }

    public function description(string $description): self
    {
        $this->data->description = $description;

        return $this;
    }

    public function noindex(): self
    {
        $this->data->robots = 'noindex, nofollow';

        return $this;
    }

    /**
     * @param  string|RobotsRule|array<int, string|RobotsRule>  $directives
     */
    public function robots(string|RobotsRule|array $directives): self
    {
        $this->data->robots = self::robotsToString($directives);

        return $this;
    }

    /**
     * @internal Also used by the route macro to normalize before caching.
     *
     * @param  string|RobotsRule|array<int, string|RobotsRule>  $directives
     */
    public static function robotsToString(string|RobotsRule|array $directives): string
    {
        $directives = is_array($directives) ? $directives : [$directives];

        return implode(', ', array_map(
            static fn (string|RobotsRule $directive): string => $directive instanceof RobotsRule
                ? $directive->value
                : $directive,
            $directives,
        ));
    }

    public function canonical(string $url): self
    {
        $this->data->canonical = $url;

        return $this;
    }

    public function image(
        string $url,
        ?int $width = null,
        ?int $height = null,
        ?string $type = null,
        ?string $alt = null,
    ): self {
        $this->data->image = $url;
        $this->data->imageWidth = $width;
        $this->data->imageHeight = $height;
        $this->data->imageType = $type;
        $this->data->imageAlt = $alt;

        return $this;
    }

    public function meta(string $name, string $content): self
    {
        $this->data->meta[$name] = $content;

        return $this;
    }

    public function prev(string $url): self
    {
        $this->data->prev = $url;

        return $this;
    }

    public function next(string $url): self
    {
        $this->data->next = $url;

        return $this;
    }

    public function paginate(LengthAwarePaginator $paginator): self
    {
        if ($paginator->currentPage() > 1) {
            $this->prev($paginator->previousPageUrl());
        }

        if ($paginator->hasMorePages()) {
            $this->next($paginator->nextPageUrl());
        }

        return $this;
    }

    public function from(Seoable $model): self
    {
        $model->toSeo($this);

        return $this;
    }

    public function alternate(string $hreflang, string $url): self
    {
        $this->data->alternates[$hreflang] = $url;

        return $this;
    }

    /**
     * @param  array<string, string>  $alternates  ['hreflang' => 'url']
     */
    public function alternates(array $alternates): self
    {
        $this->data->alternates = array_merge($this->data->alternates, $alternates);

        return $this;
    }

    /**
     * @param  list<string>  $params  Query param names/patterns to strip from auto-canonical
     */
    public function canonicalWithout(array $params): self
    {
        $this->data->canonicalStripExtra = array_merge($this->data->canonicalStripExtra, $params);

        return $this;
    }

    public function preconnect(string $url, bool $crossorigin = false): self
    {
        $this->data->preconnects[] = ['url' => $url, 'crossorigin' => $crossorigin];

        return $this;
    }

    public function dnsPrefetch(string $url): self
    {
        $this->data->dnsPrefetches[] = $url;

        return $this;
    }

    public function og(): OpenGraphProxy
    {
        return new OpenGraphProxy($this->data, $this);
    }

    public function twitter(): TwitterProxy
    {
        return new TwitterProxy($this->data, $this);
    }

    public function jsonLd(?string $type = null): JsonLdBlock|JsonLdHelper
    {
        if ($type === null) {
            return new JsonLdHelper($this->data->jsonLd, $this);
        }

        $block = new JsonLdBlock($type);
        $this->data->jsonLd->add($block);

        return $block;
    }

    public function jsonLdSeparate(string $type): JsonLdBlock
    {
        $block = new JsonLdBlock($type);
        $this->data->jsonLd->addSeparate($block);

        return $block;
    }

    /**
     * @param  array<string, ?string>  $items  ['Label' => '/url'] — null url for current page
     */
    public function breadcrumbs(array $items): self
    {
        $list = JsonLdBlock::make('BreadcrumbList');
        $elements = [];
        $position = 1;

        foreach ($items as $label => $url) {
            $item = JsonLdBlock::make('ListItem')
                ->value('position', $position++)
                ->value('name', $label);

            if ($url !== null) {
                $item->value('item', $url);
            }

            $elements[] = $item;
        }

        $list->value('itemListElement', $elements);
        $this->data->jsonLd->add($list);

        return $this;
    }

    public function render(): SeoOutput
    {
        $parts = array_filter([
            $this->renderMeta()->toHtml(),
            $this->renderOpenGraph()->toHtml(),
            $this->renderTwitter()->toHtml(),
            $this->renderJsonLd()->toHtml(),
        ]);

        return new SeoOutput(implode("\n", $parts));
    }

    public function renderMeta(): SeoOutput
    {
        $lines = [];

        $lines[] = '<title>'.e($this->resolveTitle()).'</title>';

        $description = $this->resolveDescription();
        if ($description) {
            $lines[] = '<meta name="description" content="'.e($description).'">';
        }

        $canonical = $this->resolveCanonical();
        if ($canonical) {
            $lines[] = '<link rel="canonical" href="'.e($canonical).'">';
        }

        $robots = $this->resolveRobots();
        if ($robots) {
            $lines[] = '<meta name="robots" content="'.e($robots).'">';
        }

        if ($this->data->prev) {
            $lines[] = '<link rel="prev" href="'.e($this->data->prev).'">';
        }

        if ($this->data->next) {
            $lines[] = '<link rel="next" href="'.e($this->data->next).'">';
        }

        foreach ($this->data->meta as $name => $content) {
            $lines[] = '<meta name="'.e($name).'" content="'.e($content).'">';
        }

        foreach ($this->data->alternates as $hreflang => $url) {
            $lines[] = '<link rel="alternate" hreflang="'.e($hreflang).'" href="'.e($url).'">';
        }

        foreach ($this->data->preconnects as $preconnect) {
            $tag = '<link rel="preconnect" href="'.e($preconnect['url']).'"';
            if ($preconnect['crossorigin']) {
                $tag .= ' crossorigin';
            }
            $lines[] = $tag.'>';
        }

        foreach ($this->data->dnsPrefetches as $url) {
            $lines[] = '<link rel="dns-prefetch" href="'.e($url).'">';
        }

        return new SeoOutput(implode("\n", $lines));
    }

    public function renderOpenGraph(): SeoOutput
    {
        $lines = [];

        $lines[] = '<meta property="og:type" content="'.e($this->resolveOgType()).'">';
        $lines[] = '<meta property="og:site_name" content="'.e($this->defaults->siteName).'">';

        $lines[] = '<meta property="og:title" content="'.e($this->resolveOgTitle()).'">';

        $ogDescription = $this->resolveOgDescription();
        if ($ogDescription) {
            $lines[] = '<meta property="og:description" content="'.e($ogDescription).'">';
        }

        $url = $this->resolveCanonical();
        if ($url) {
            $lines[] = '<meta property="og:url" content="'.e($url).'">';
        }

        if ($this->data->ogLocale !== null) {
            $lines[] = '<meta property="og:locale" content="'.e($this->data->ogLocale).'">';
        }

        foreach ($this->data->ogAlternateLocales as $locale) {
            $lines[] = '<meta property="og:locale:alternate" content="'.e($locale).'">';
        }

        $image = $this->resolveOgImage();
        if ($image) {
            $lines[] = '<meta property="og:image" content="'.e($image).'">';

            if ($this->data->imageWidth !== null) {
                $lines[] = '<meta property="og:image:width" content="'.$this->data->imageWidth.'">';
            }

            if ($this->data->imageHeight !== null) {
                $lines[] = '<meta property="og:image:height" content="'.$this->data->imageHeight.'">';
            }

            if ($this->data->imageType !== null) {
                $lines[] = '<meta property="og:image:type" content="'.e($this->data->imageType).'">';
            }

            if ($this->data->imageAlt !== null) {
                $lines[] = '<meta property="og:image:alt" content="'.e($this->data->imageAlt).'">';
            }
        }

        return new SeoOutput(implode("\n", $lines));
    }

    public function renderTwitter(): SeoOutput
    {
        $lines = [];

        $lines[] = '<meta name="twitter:card" content="'.e($this->defaults->twitterCard).'">';

        $lines[] = '<meta name="twitter:title" content="'.e($this->resolveTwitterTitle()).'">';

        $twitterDescription = $this->resolveTwitterDescription();
        if ($twitterDescription) {
            $lines[] = '<meta name="twitter:description" content="'.e($twitterDescription).'">';
        }

        $image = $this->resolveTwitterImage();
        if ($image) {
            $lines[] = '<meta name="twitter:image" content="'.e($image).'">';
        }

        if ($this->defaults->twitterSite) {
            $lines[] = '<meta name="twitter:site" content="'.e($this->defaults->twitterSite).'">';
        }

        return new SeoOutput(implode("\n", $lines));
    }

    public function renderJsonLd(): SeoOutput
    {
        if ($this->data->jsonLd->isEmpty()) {
            return new SeoOutput('');
        }

        return new SeoOutput($this->data->jsonLd->render());
    }

    /**
     * All resolved (post-cascade) values as a structured array. JSON-LD is
     * excluded; use renderJsonLd() for that.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->resolveTitle(),
            'description' => $this->resolveDescription(),
            'canonical' => $this->resolveCanonical(),
            'robots' => $this->resolveRobots(),
            'prev' => $this->data->prev,
            'next' => $this->data->next,
            'meta' => $this->data->meta,
            'alternates' => $this->data->alternates,
            'og' => [
                'type' => $this->resolveOgType(),
                'site_name' => $this->defaults->siteName,
                'title' => $this->resolveOgTitle(),
                'description' => $this->resolveOgDescription(),
                'url' => $this->resolveCanonical(),
                'locale' => $this->data->ogLocale,
                'alternate_locales' => $this->data->ogAlternateLocales,
                'image' => $this->resolveOgImage(),
                'image_width' => $this->data->imageWidth,
                'image_height' => $this->data->imageHeight,
                'image_type' => $this->data->imageType,
                'image_alt' => $this->data->imageAlt,
            ],
            'twitter' => [
                'card' => $this->defaults->twitterCard,
                'title' => $this->resolveTwitterTitle(),
                'description' => $this->resolveTwitterDescription(),
                'image' => $this->resolveTwitterImage(),
                'site' => $this->defaults->twitterSite,
            ],
        ];
    }

    /**
     * Route-level metadata set via the Route::seo() macro or a 'seo' group
     * attribute. Read lazily: rendering happens in views, long after routing.
     */
    private function routeSeo(string $field): ?string
    {
        if ($this->routeSeo === null) {
            $route = app()->bound('request') ? request()->route() : null;
            $raw = $route instanceof Route ? $route->getAction(RouteSeo::KEY) : null;

            $this->routeSeo = is_array($raw) ? RouteSeo::normalize($raw) : [];
        }

        return $this->routeSeo[$field] ?? null;
    }

    private function resolveTitle(): string
    {
        $title = $this->data->title ?? $this->routeSeo('title') ?? $this->defaults->title;

        if ($title === null) {
            return $this->defaults->siteName;
        }

        return $this->data->titleExact
            ? $title
            : $title.$this->defaults->separator.$this->defaults->siteName;
    }

    private function resolveDescription(): ?string
    {
        return $this->data->description ?? $this->routeSeo('description') ?? $this->defaults->description;
    }

    private function resolveRobots(): ?string
    {
        return $this->data->robots ?? $this->routeSeo('robots') ?? $this->defaults->robots;
    }

    private function resolveOgType(): string
    {
        return $this->data->ogType ?? $this->routeSeo('og_type') ?? $this->defaults->ogType;
    }

    private function resolveOgTitle(): string
    {
        return $this->data->ogTitle ?? $this->resolveTitle();
    }

    private function resolveOgDescription(): ?string
    {
        return $this->data->ogDescription ?? $this->resolveDescription();
    }

    private function resolveOgImage(): ?string
    {
        return $this->data->image ?? $this->defaults->ogImage;
    }

    private function resolveTwitterTitle(): string
    {
        return $this->data->twitterTitle ?? $this->resolveTitle();
    }

    private function resolveTwitterDescription(): ?string
    {
        return $this->data->twitterDescription ?? $this->resolveDescription();
    }

    private function resolveTwitterImage(): ?string
    {
        return $this->data->image ?? $this->defaults->twitterImage;
    }

    private function resolveCanonical(): ?string
    {
        if ($this->data->canonical) {
            return $this->data->canonical;
        }

        if ($routeCanonical = $this->routeSeo('canonical')) {
            return $routeCanonical;
        }

        if ($this->defaults->autoCanonical) {
            $patterns = array_merge($this->defaults->canonicalStrip, $this->data->canonicalStripExtra);

            if (! empty($patterns)) {
                return $this->stripCanonicalParams(request()->fullUrl());
            }

            return url()->current();
        }

        return null;
    }

    private function stripCanonicalParams(string $url): string
    {
        $parsed = parse_url($url);

        if (! isset($parsed['query'])) {
            return $url;
        }

        $patterns = array_merge($this->defaults->canonicalStrip, $this->data->canonicalStripExtra);

        parse_str($parsed['query'], $params);

        foreach (array_keys($params) as $key) {
            foreach ($patterns as $pattern) {
                if (fnmatch($pattern, $key)) {
                    unset($params[$key]);
                    break;
                }
            }
        }

        $base = strtok($url, '?');

        return empty($params) ? $base : $base.'?'.http_build_query($params);
    }
}

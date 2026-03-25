<?php

namespace ThreeOhEight\Seo;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Traits\Macroable;
use ThreeOhEight\Seo\Contracts\Seoable;
use ThreeOhEight\Seo\JsonLd\JsonLdBlock;
use ThreeOhEight\Seo\JsonLd\JsonLdHelper;

class Seo
{
    use Macroable {
        __call as macroCall;
    }

    public function __construct(
        private SeoData $data,
        private readonly SeoDefaults $defaults,
    ) {}

    public function title(string $title): self
    {
        $this->data->title = $title;

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

    public function robots(string $robots): self
    {
        $this->data->robots = $robots;

        return $this;
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

        $lines[] = '<title>'.e($this->formatTitle($this->data->title ?? $this->defaults->title)).'</title>';

        $description = $this->data->description ?? $this->defaults->description;
        if ($description) {
            $lines[] = '<meta name="description" content="'.e($description).'">';
        }

        $canonical = $this->resolveCanonical();
        if ($canonical) {
            $lines[] = '<link rel="canonical" href="'.e($canonical).'">';
        }

        $robots = $this->data->robots ?? $this->defaults->robots;
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

        $lines[] = '<meta property="og:type" content="'.e($this->defaults->ogType).'">';
        $lines[] = '<meta property="og:site_name" content="'.e($this->defaults->siteName).'">';

        $ogTitle = $this->data->ogTitle
            ?? $this->formatTitle($this->data->title);
        $lines[] = '<meta property="og:title" content="'.e($ogTitle).'">';

        $ogDescription = $this->data->ogDescription
            ?? $this->data->description
            ?? $this->defaults->description;
        if ($ogDescription) {
            $lines[] = '<meta property="og:description" content="'.e($ogDescription).'">';
        }

        $url = $this->resolveCanonical();
        if ($url) {
            $lines[] = '<meta property="og:url" content="'.e($url).'">';
        }

        $image = $this->data->image ?? $this->defaults->ogImage;
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

        $twitterTitle = $this->data->twitterTitle
            ?? $this->formatTitle($this->data->title);
        $lines[] = '<meta name="twitter:title" content="'.e($twitterTitle).'">';

        $twitterDescription = $this->data->twitterDescription
            ?? $this->data->description
            ?? $this->defaults->description;
        if ($twitterDescription) {
            $lines[] = '<meta name="twitter:description" content="'.e($twitterDescription).'">';
        }

        $image = $this->data->image ?? $this->defaults->twitterImage;
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

    private function formatTitle(?string $pageTitle): string
    {
        if ($pageTitle) {
            return $pageTitle.$this->defaults->separator.$this->defaults->siteName;
        }

        return $this->defaults->siteName;
    }

    private function resolveCanonical(): ?string
    {
        if ($this->data->canonical) {
            return $this->data->canonical;
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

<?php

namespace ThreeOhEight\Seo;

use ThreeOhEight\Seo\JsonLd\JsonLdBlock;

class Seo
{
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

    public function image(string $url): self
    {
        $this->data->image = $url;

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

    public function jsonLd(string $type): JsonLdBlock
    {
        $block = new JsonLdBlock($type);
        $this->data->jsonLd->add($block);

        return $block;
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

        $title = $this->data->title ?? $this->defaults->title;
        if ($title) {
            $lines[] = '<title>'.$title.$this->defaults->separator.$this->defaults->siteName.'</title>';
        } else {
            $lines[] = '<title>'.$this->defaults->siteName.'</title>';
        }

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

        return new SeoOutput(implode("\n", $lines));
    }

    public function renderOpenGraph(): SeoOutput
    {
        $lines = [];

        $lines[] = '<meta property="og:type" content="'.e($this->defaults->ogType).'">';
        $lines[] = '<meta property="og:site_name" content="'.e($this->defaults->siteName).'">';

        $ogTitle = $this->data->ogTitle
            ?? ($this->data->title ? $this->data->title.$this->defaults->suffix : null)
            ?? $this->defaults->siteName;
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
        }

        return new SeoOutput(implode("\n", $lines));
    }

    public function renderTwitter(): SeoOutput
    {
        $lines = [];

        $lines[] = '<meta name="twitter:card" content="'.e($this->defaults->twitterCard).'">';

        $twitterTitle = $this->data->twitterTitle
            ?? ($this->data->title ? $this->data->title.$this->defaults->suffix : null)
            ?? $this->defaults->siteName;
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

    private function resolveCanonical(): ?string
    {
        if ($this->data->canonical) {
            return $this->data->canonical;
        }

        if ($this->defaults->autoCanonical) {
            return url()->current();
        }

        return null;
    }
}

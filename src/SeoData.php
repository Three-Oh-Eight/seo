<?php

namespace ThreeOhEight\Seo;

use ThreeOhEight\Seo\JsonLd\JsonLdCollection;

class SeoData
{
    public ?string $title = null;

    public ?string $description = null;

    public ?string $robots = null;

    public ?string $canonical = null;

    public ?string $image = null;

    public ?string $ogTitle = null;

    public ?string $ogDescription = null;

    public ?string $twitterTitle = null;

    public ?string $twitterDescription = null;

    // Image dimensions (Feature 3)
    public ?int $imageWidth = null;

    public ?int $imageHeight = null;

    public ?string $imageType = null;

    public ?string $imageAlt = null;

    /** @var array<string, string> */
    public array $meta = [];

    public ?string $prev = null;

    public ?string $next = null;

    /** @var array<string, string> hreflang => url */
    public array $alternates = [];

    /** @var list<string> */
    public array $canonicalStripExtra = [];

    /** @var list<array{url: string, crossorigin: bool}> */
    public array $preconnects = [];

    /** @var list<string> */
    public array $dnsPrefetches = [];

    public JsonLdCollection $jsonLd;

    public function __construct()
    {
        $this->jsonLd = new JsonLdCollection;
    }
}

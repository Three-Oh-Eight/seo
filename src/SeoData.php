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

    public JsonLdCollection $jsonLd;

    public function __construct()
    {
        $this->jsonLd = new JsonLdCollection;
    }
}

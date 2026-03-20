<?php

namespace ThreeOhEight\Seo\Facades;

use Illuminate\Support\Facades\Facade;
use ThreeOhEight\Seo\JsonLd\JsonLdBlock;
use ThreeOhEight\Seo\OpenGraphProxy;
use ThreeOhEight\Seo\SeoOutput;
use ThreeOhEight\Seo\TwitterProxy;

/**
 * @method static \ThreeOhEight\Seo\Seo title(string $title)
 * @method static \ThreeOhEight\Seo\Seo description(string $description)
 * @method static \ThreeOhEight\Seo\Seo noindex()
 * @method static \ThreeOhEight\Seo\Seo robots(string $robots)
 * @method static \ThreeOhEight\Seo\Seo canonical(string $url)
 * @method static \ThreeOhEight\Seo\Seo image(string $url)
 * @method static OpenGraphProxy og()
 * @method static TwitterProxy twitter()
 * @method static JsonLdBlock jsonLd(string $type)
 * @method static SeoOutput render()
 * @method static SeoOutput renderMeta()
 * @method static SeoOutput renderOpenGraph()
 * @method static SeoOutput renderTwitter()
 * @method static SeoOutput renderJsonLd()
 */
class Seo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \ThreeOhEight\Seo\Seo::class;
    }
}

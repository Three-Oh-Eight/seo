<?php

namespace ThreeOhEight\Seo\Facades;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Facade;
use ThreeOhEight\Seo\Contracts\Seoable;
use ThreeOhEight\Seo\JsonLd\JsonLdBlock;
use ThreeOhEight\Seo\JsonLd\JsonLdHelper;
use ThreeOhEight\Seo\OpenGraphProxy;
use ThreeOhEight\Seo\SeoOutput;
use ThreeOhEight\Seo\TwitterProxy;

/**
 * @method static \ThreeOhEight\Seo\Seo title(string $title)
 * @method static \ThreeOhEight\Seo\Seo description(string $description)
 * @method static \ThreeOhEight\Seo\Seo noindex()
 * @method static \ThreeOhEight\Seo\Seo robots(string $robots)
 * @method static \ThreeOhEight\Seo\Seo canonical(string $url)
 * @method static \ThreeOhEight\Seo\Seo image(string $url, ?int $width = null, ?int $height = null, ?string $type = null, ?string $alt = null)
 * @method static \ThreeOhEight\Seo\Seo meta(string $name, string $content)
 * @method static \ThreeOhEight\Seo\Seo prev(string $url)
 * @method static \ThreeOhEight\Seo\Seo next(string $url)
 * @method static \ThreeOhEight\Seo\Seo paginate(LengthAwarePaginator $paginator)
 * @method static \ThreeOhEight\Seo\Seo from(Seoable $model)
 * @method static \ThreeOhEight\Seo\Seo breadcrumbs(array $items)
 * @method static \ThreeOhEight\Seo\Seo alternate(string $hreflang, string $url)
 * @method static \ThreeOhEight\Seo\Seo alternates(array $alternates)
 * @method static \ThreeOhEight\Seo\Seo canonicalWithout(array $params)
 * @method static \ThreeOhEight\Seo\Seo preconnect(string $url, bool $crossorigin = false)
 * @method static \ThreeOhEight\Seo\Seo dnsPrefetch(string $url)
 * @method static OpenGraphProxy og()
 * @method static TwitterProxy twitter()
 * @method static JsonLdBlock|JsonLdHelper jsonLd(?string $type = null)
 * @method static JsonLdBlock jsonLdSeparate(string $type)
 * @method static SeoOutput render()
 * @method static SeoOutput renderMeta()
 * @method static SeoOutput renderOpenGraph()
 * @method static SeoOutput renderTwitter()
 * @method static SeoOutput renderJsonLd()
 * @method static void macro(string $name, object|callable $macro)
 *
 * @see \ThreeOhEight\Seo\Seo
 */
class Seo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \ThreeOhEight\Seo\Seo::class;
    }
}

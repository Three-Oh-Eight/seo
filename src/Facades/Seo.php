<?php

namespace ThreeOhEight\Seo\Facades;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Facade;
use ThreeOhEight\Seo\Contracts\Seoable;
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
 * @method static \ThreeOhEight\Seo\Seo meta(string $name, string $content)
 * @method static \ThreeOhEight\Seo\Seo prev(string $url)
 * @method static \ThreeOhEight\Seo\Seo next(string $url)
 * @method static \ThreeOhEight\Seo\Seo paginate(LengthAwarePaginator $paginator)
 * @method static \ThreeOhEight\Seo\Seo from(Seoable $model)
 * @method static \ThreeOhEight\Seo\Seo breadcrumbs(array $items)
 * @method static OpenGraphProxy og()
 * @method static TwitterProxy twitter()
 * @method static JsonLdBlock jsonLd(string $type)
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

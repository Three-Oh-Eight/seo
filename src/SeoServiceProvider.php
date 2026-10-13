<?php

namespace ThreeOhEight\Seo;

use Illuminate\Support\ServiceProvider;
use Laravel\Head\Facades\Schema;
use Laravel\Head\HeadBuilder;
use Laravel\Head\HeadManager;
use Laravel\Head\TagRegistry;
use ThreeOhEight\Seo\Contracts\Seoable;
use ThreeOhEight\Seo\Http\ServerCardController;
use ThreeOhEight\Seo\Schema\Answer;
use ThreeOhEight\Seo\Schema\QaPage;
use ThreeOhEight\Seo\Schema\Question;
use ThreeOhEight\Seo\Schema\SoftwareApplication;
use ThreeOhEight\Seo\Schema\WebApplication;
use ThreeOhEight\Seo\Tags\OgUrl;

/**
 * Thin add-on on top of laravel/head. Head resolves every page as
 * defaults, then route withHead() metadata, then runtime Head:: calls;
 * this provider only feeds config/seo.php into the defaults layer and adds
 * a few Head macros.
 */
class SeoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/seo.php', 'seo');

        // HeadManager is a singleton whose per-request state lives in a scoped
        // binding, so registering the config defaults once is Octane safe.
        $this->callAfterResolving(HeadManager::class, function (HeadManager $head): void {
            $head->defaults(fn (HeadBuilder $defaults) => $this->applyConfigDefaults($defaults));
        });

        // Same registry Head::extend() writes to, hooked on the singleton
        // itself so the builder is added exactly once, even when the head
        // manager is re-resolved.
        $this->callAfterResolving(TagRegistry::class, fn (TagRegistry $tags) => $tags->extend(OgUrl::class));
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/seo.php' => config_path('seo.php'),
        ], 'seo-config');

        $this->registerHeadMacros();

        $this->registerSchemaTypes();

        $this->registerServerCardRoutes();
    }

    /**
     * Typed builders for rich results head does not ship, available as
     * Schema::softwareApplication(), webApplication(), qaPage(), question()
     * and answer().
     */
    private function registerSchemaTypes(): void
    {
        foreach ([
            SoftwareApplication::class,
            WebApplication::class,
            QaPage::class,
            Question::class,
            Answer::class,
        ] as $type) {
            Schema::register($type);
        }
    }

    private function applyConfigDefaults(HeadBuilder $head): void
    {
        $config = (array) $this->app['config']->get('seo', []);

        $siteName = self::stringOrNull($config['site_name'] ?? null);
        $suffix = $siteName !== null ? ($config['separator'] ?? ' - ').$siteName : null;
        $title = self::stringOrNull($config['title'] ?? null);

        // Head renders a default title exactly and only decorates page titles
        // with the suffix, so the configured fallback title is pre-composed.
        $defaultTitle = $title !== null ? $title.$suffix : $siteName;

        if ($defaultTitle !== null) {
            $head->title($defaultTitle, suffix: $suffix);
        }

        if ($description = self::stringOrNull($config['description'] ?? null)) {
            $head->description($description);
        }

        if ($robots = self::stringOrNull($config['robots'] ?? null)) {
            $head->robots($robots);
        }

        if ($config['auto_canonical'] ?? true) {
            $head->canonical();
        }

        $head->og(type: self::stringOrNull($config['og_type'] ?? null), siteName: $siteName);

        $ogImage = self::stringOrNull($config['og_image'] ?? null);

        if ($ogImage !== null) {
            $head->ogImage($ogImage);
        }

        $head->twitter(
            card: self::stringOrNull($config['twitter_card'] ?? null),
            site: self::stringOrNull($config['twitter_site'] ?? null),
        );

        // twitter:image already falls back to the og:image, and an explicit
        // default would shadow a page-level Head::ogImage(), so only set it
        // when the config asks for a different image.
        $twitterImage = self::stringOrNull($config['twitter_image'] ?? null);

        if ($twitterImage !== null && $twitterImage !== $ogImage) {
            $head->twitterImage($twitterImage);
        }
    }

    private function registerHeadMacros(): void
    {
        HeadManager::macro('from', function (Seoable $model): HeadManager {
            /** @var HeadManager $this */
            $model->toSeo($this);

            return $this;
        });

        /*
         * Canonical URL of the current request that keeps its query string,
         * minus the seo.canonical_strip patterns and the given ones (fnmatch
         * syntax, eg "utm_*"). Head's automatic canonical drops the whole
         * query string; use this on pages where some parameters matter.
         */
        HeadManager::macro('canonicalWithout', function (array $params = []): HeadManager {
            /** @var HeadManager $this */
            $patterns = [...(array) config('seo.canonical_strip', []), ...$params];

            return $this->canonical(CanonicalUrl::without(request()->fullUrl(), $patterns));
        });
    }

    /**
     * The opt-in MCP server-card discovery endpoints. All three probe paths
     * answer identically because SEP-2127's canonical path has moved between
     * draft revisions; serving every candidate is cheap.
     */
    private function registerServerCardRoutes(): void
    {
        if (! $this->app['config']->get('seo.server_card.enabled', false)) {
            return;
        }

        $router = $this->app['router'];

        foreach ([
            '/.well-known/mcp.json',
            '/.well-known/mcp',
            '/.well-known/mcp/server-card.json',
        ] as $path) {
            $router->get($path, ServerCardController::class);
        }
    }

    private static function stringOrNull(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}

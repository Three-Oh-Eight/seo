<?php

namespace ThreeOhEight\Seo;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use ThreeOhEight\Seo\Http\ServerCardController;
use ThreeOhEight\Seo\Middleware\SeoMiddleware;
use ThreeOhEight\Seo\Routing\RouteSeo;

class SeoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/seo.php', 'seo');

        $this->app->scoped(Seo::class, function ($app) {
            $defaults = SeoDefaults::fromConfig($app['config']->get('seo', []));

            return new Seo(new SeoData, $defaults);
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/seo.php' => config_path('seo.php'),
        ], 'seo-config');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'seo');

        Blade::directive('seo', function () {
            return "<?php echo app(\ThreeOhEight\Seo\Seo::class)->render(); ?>";
        });

        $this->app['router']->aliasMiddleware('seo', SeoMiddleware::class);

        $this->registerRouteMacro();

        $this->registerServerCardRoutes();
    }

    /**
     * Route-level SEO metadata: Route::get(...)->seo(title: 'Pricing'). Values
     * are normalized to plain scalars in the route action so they survive
     * route:cache. Group syntax: Route::group(['seo' => [...]], ...) with
     * plain scalars only.
     */
    private function registerRouteMacro(): void
    {
        Route::macro('seo', function (
            ?string $title = null,
            ?string $description = null,
            string|RobotsRule|array|null $robots = null,
            ?string $canonical = null,
            ?bool $noindex = null,
            ?string $ogType = null,
        ): Route {
            /** @var Route $this */
            $values = array_filter([
                'title' => $title,
                'description' => $description,
                'robots' => $robots !== null ? Seo::robotsToString($robots) : null,
                'canonical' => $canonical,
                'noindex' => $noindex,
                'og_type' => $ogType,
            ], static fn ($value) => $value !== null);

            // Group attributes are merged into the action before this macro
            // runs, so a plain array_merge makes route values beat group values.
            $this->action[RouteSeo::KEY] = array_merge($this->action[RouteSeo::KEY] ?? [], $values);

            return $this;
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
}

<?php

namespace ThreeOhEight\Seo;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

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

        $this->app['router']->aliasMiddleware('seo', \ThreeOhEight\Seo\Middleware\SeoMiddleware::class);

        $this->registerServerCardRoutes();
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
            $router->get($path, \ThreeOhEight\Seo\Http\ServerCardController::class);
        }
    }
}

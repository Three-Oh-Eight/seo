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
    }
}

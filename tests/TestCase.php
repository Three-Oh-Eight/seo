<?php

namespace ThreeOhEight\Seo\Tests;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadManager;
use Laravel\Head\HeadServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use ThreeOhEight\Seo\SeoServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            HeadServiceProvider::class,
            SeoServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        // Livewire checksums its snapshots with the encrypter.
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('s', 32)));

        foreach ([
            'site_name' => 'TestSite',
            'separator' => ' - ',
            'title' => null,
            'description' => 'Default description',
            'auto_canonical' => true,
            'robots' => 'index, follow',
            'og_type' => 'website',
            'og_image' => 'https://cdn.test/og.png',
            'twitter_card' => 'summary_large_image',
            'twitter_image' => null,
            'twitter_site' => '@testsite',
            'canonical_strip' => [],
        ] as $key => $value) {
            $app['config']->set("seo.{$key}", $value);
        }
    }

    /**
     * Apply config overrides and re-resolve head, so the defaults layer is
     * rebuilt from the new config the way a fresh application would.
     *
     * @param  array<string, mixed>  $config
     */
    protected function withSeoConfig(array $config): void
    {
        foreach ($config as $key => $value) {
            config()->set("seo.{$key}", $value);
        }

        $this->app->forgetInstance(HeadManager::class);
        Head::clearResolvedInstance();
    }

    /**
     * Register a GET route that renders the @head directive, optionally
     * running a callback first (the runtime layer).
     */
    protected function headRoute(string $uri, ?\Closure $runtime = null): \Illuminate\Routing\Route
    {
        return Route::get($uri, function () use ($runtime): string {
            if ($runtime !== null) {
                $runtime();
            }

            return Blade::render('@head');
        });
    }
}

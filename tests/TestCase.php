<?php

namespace ThreeOhEight\Seo\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use ThreeOhEight\Seo\Seo;
use ThreeOhEight\Seo\SeoData;
use ThreeOhEight\Seo\SeoDefaults;
use ThreeOhEight\Seo\SeoServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [SeoServiceProvider::class];
    }

    protected function makeSeo(array $configOverrides = []): Seo
    {
        $config = array_merge([
            'site_name' => 'TestSite',
            'separator' => ' - ',
            'title' => null,
            'description' => null,
            'auto_canonical' => false,
            'robots' => null,
            'og_type' => 'website',
            'og_image' => null,
            'twitter_card' => 'summary_large_image',
            'twitter_image' => null,
            'twitter_site' => null,
        ], $configOverrides);

        $defaults = SeoDefaults::fromConfig($config);

        return new Seo(new SeoData, $defaults);
    }
}

<?php

namespace ThreeOhEight\Seo\Concerns;

use ThreeOhEight\Seo\Contracts\Seoable;
use ThreeOhEight\Seo\Seo;

/**
 * Livewire trait that auto-applies SEO from Seoable properties.
 *
 * Auto-detects public Seoable properties, or override seo(Seo $seo) for custom logic.
 */
trait WithSeo
{
    public function renderingWithSeo(): void
    {
        $seo = app(Seo::class);

        if (method_exists($this, 'seo')) {
            $this->seo($seo);

            return;
        }

        foreach ((new \ReflectionClass($this))->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $value = $property->getValue($this);
            if ($value instanceof Seoable) {
                $value->toSeo($seo);
            }
        }
    }
}

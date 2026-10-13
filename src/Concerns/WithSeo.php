<?php

namespace ThreeOhEight\Seo\Concerns;

use Laravel\Head\Facades\Head;
use Laravel\Head\HeadManager;
use ReflectionClass;
use ReflectionProperty;
use ThreeOhEight\Seo\Contracts\Seoable;

/**
 * Livewire trait that applies head data before every render.
 *
 * Define seo(HeadManager $head) for custom logic; otherwise every public
 * Seoable property is applied.
 */
trait WithSeo
{
    public function renderingWithSeo(): void
    {
        /** @var HeadManager $head */
        $head = Head::getFacadeRoot();

        if (method_exists($this, 'seo')) {
            $this->seo($head);

            return;
        }

        foreach ((new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic() || ! $property->isInitialized($this)) {
                continue;
            }

            $value = $property->getValue($this);

            if ($value instanceof Seoable) {
                $value->toSeo($head);
            }
        }
    }
}

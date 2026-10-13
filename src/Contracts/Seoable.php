<?php

namespace ThreeOhEight\Seo\Contracts;

use Laravel\Head\HeadManager;

/**
 * A model (or any object) that knows how to describe itself in the page head.
 * Applied with Head::from($model) or automatically by the WithSeo Livewire trait.
 */
interface Seoable
{
    public function toSeo(HeadManager $head): void;
}

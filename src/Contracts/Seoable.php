<?php

namespace ThreeOhEight\Seo\Contracts;

use ThreeOhEight\Seo\Seo;

interface Seoable
{
    public function toSeo(Seo $seo): void;
}

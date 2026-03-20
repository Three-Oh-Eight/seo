<?php

namespace ThreeOhEight\Seo\Concerns;

use ThreeOhEight\Seo\Seo;

/**
 * Default Seoable implementation for Eloquent models.
 *
 * Pulls from meta_title/title and meta_description/description/excerpt columns.
 * Override toSeo() for custom logic.
 */
trait HasSeo
{
    public function toSeo(Seo $seo): void
    {
        $title = $this->meta_title ?? $this->title ?? null;
        if ($title) {
            $seo->title($title);
        }

        $description = $this->meta_description ?? $this->description ?? $this->excerpt ?? null;
        if ($description) {
            $seo->description($description);
        }
    }
}

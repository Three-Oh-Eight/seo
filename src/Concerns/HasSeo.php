<?php

namespace ThreeOhEight\Seo\Concerns;

use Laravel\Head\HeadManager;

/**
 * Default Seoable implementation for Eloquent models.
 *
 * Pulls from meta_title/title and meta_description/description/excerpt
 * attributes. Override toSeo() for custom logic.
 */
trait HasSeo
{
    public function toSeo(HeadManager $head): void
    {
        $title = $this->meta_title ?? $this->title ?? null;

        if (is_string($title) && $title !== '') {
            $head->title($title);
        }

        $description = $this->meta_description ?? $this->description ?? $this->excerpt ?? null;

        if (is_string($description) && $description !== '') {
            $head->description($description);
        }
    }
}

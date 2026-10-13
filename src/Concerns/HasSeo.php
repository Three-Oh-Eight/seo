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
        $title = $this->firstFilledSeoString($this->meta_title, $this->title);

        if ($title !== null) {
            $head->title($title);
        }

        $description = $this->firstFilledSeoString($this->meta_description, $this->description, $this->excerpt);

        if ($description !== null) {
            $head->description($description);
        }
    }

    /**
     * First candidate that is a string and not null, empty or whitespace-only.
     */
    private function firstFilledSeoString(mixed ...$candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (is_string($candidate) && filled($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}

<?php

namespace ThreeOhEight\Seo;

class TwitterProxy
{
    public function __construct(
        private SeoData $data,
        private Seo $seo,
    ) {}

    public function title(string $title): Seo
    {
        $this->data->twitterTitle = $title;

        return $this->seo;
    }

    public function description(string $description): Seo
    {
        $this->data->twitterDescription = $description;

        return $this->seo;
    }
}

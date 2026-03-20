<?php

namespace ThreeOhEight\Seo;

class OpenGraphProxy
{
    public function __construct(
        private SeoData $data,
        private Seo $seo,
    ) {}

    public function title(string $title): Seo
    {
        $this->data->ogTitle = $title;

        return $this->seo;
    }

    public function description(string $description): Seo
    {
        $this->data->ogDescription = $description;

        return $this->seo;
    }
}

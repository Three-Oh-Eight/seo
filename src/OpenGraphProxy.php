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

    public function locale(string $locale): Seo
    {
        $this->data->ogLocale = $locale;

        return $this->seo;
    }

    /**
     * @param  string|array<int, string>  $locales
     */
    public function alternateLocale(string|array $locales): Seo
    {
        foreach ((array) $locales as $locale) {
            if (! in_array($locale, $this->data->ogAlternateLocales, true)) {
                $this->data->ogAlternateLocales[] = $locale;
            }
        }

        return $this->seo;
    }
}

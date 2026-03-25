<?php

namespace ThreeOhEight\Seo;

readonly class SeoDefaults
{
    public function __construct(
        public string $siteName,
        public string $separator,
        public ?string $title,
        public ?string $description,
        public bool $autoCanonical,
        public ?string $robots,
        public string $ogType,
        public ?string $ogImage,
        public string $twitterCard,
        public ?string $twitterImage,
        public ?string $twitterSite,
        /** @var list<string> */
        public array $canonicalStrip = [],
    ) {}

    /**
     * @param  array<string, mixed>  $config
     */
    public static function fromConfig(array $config): self
    {
        return new self(
            siteName: $config['site_name'] ?? config('app.name', ''),
            separator: $config['separator'] ?? ' - ',
            title: $config['title'] ?? null,
            description: $config['description'] ?? null,
            autoCanonical: $config['auto_canonical'] ?? true,
            robots: $config['robots'] ?? null,
            ogType: $config['og_type'] ?? 'website',
            ogImage: $config['og_image'] ?? null,
            twitterCard: $config['twitter_card'] ?? 'summary_large_image',
            twitterImage: $config['twitter_image'] ?? null,
            twitterSite: $config['twitter_site'] ?? null,
            canonicalStrip: $config['canonical_strip'] ?? [],
        );
    }
}

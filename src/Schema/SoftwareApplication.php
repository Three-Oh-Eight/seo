<?php

namespace ThreeOhEight\Seo\Schema;

use DateTimeInterface;
use Laravel\Head\Schema\Offer;
use Laravel\Head\Schema\Organization;
use Laravel\Head\Schema\Person;
use Laravel\Head\Schema\SchemaObject;
use Laravel\Head\SchemaType;
use ThreeOhEight\Seo\Schema\Concerns\SetsFilledValues;

/**
 * Google's "Software app" rich result. Needs name, offers (price) and an
 * aggregateRating or review; applicationCategory and operatingSystem are
 * recommended. Null or empty input is left out.
 */
#[SchemaType('SoftwareApplication')]
class SoftwareApplication extends SchemaObject
{
    use SetsFilledValues;

    /** @var list<array<string, mixed>> */
    protected array $reviews = [];

    public function name(?string $name): static
    {
        return $this->setFilled('name', $name);
    }

    public function description(?string $description): static
    {
        return $this->setFilled('description', $description);
    }

    public function url(?string $url): static
    {
        return $this->setFilled('url', $url);
    }

    /**
     * @param  string|array<int, string>|null  $image
     */
    public function image(string|array|null $image): static
    {
        return $this->setFilled('image', $image);
    }

    /**
     * A schema.org application category, eg "BusinessApplication" or "GameApplication".
     */
    public function applicationCategory(?string $category): static
    {
        return $this->setFilled('applicationCategory', $category);
    }

    /**
     * Eg "Windows 11", "Android" or "Any" for a web application.
     */
    public function operatingSystem(?string $operatingSystem): static
    {
        return $this->setFilled('operatingSystem', $operatingSystem);
    }

    public function softwareVersion(?string $version): static
    {
        return $this->setFilled('softwareVersion', $version);
    }

    /**
     * @param  Offer|array<int, Offer>|null  $offers
     */
    public function offers(Offer|array|null $offers): static
    {
        return $this->setFilled('offers', $offers);
    }

    /**
     * Shorthand for a single Offer; use 0 for a free application.
     */
    public function price(float|int|string|null $price, ?string $currency = null): static
    {
        if ($price === null || $price === '') {
            return $this;
        }

        $offer = (new Offer)->price($price);

        return $this->offers($currency === null || $currency === '' ? $offer : $offer->currency($currency));
    }

    /**
     * Google needs a ratingCount or reviewCount alongside the value.
     */
    public function aggregateRating(
        float|int|string|null $value,
        ?int $count = null,
        ?int $reviewCount = null,
        float|int|null $best = null,
        float|int|null $worst = null,
    ): static {
        if ($value === null || $value === '') {
            return $this;
        }

        return $this->set('aggregateRating', ['@type' => 'AggregateRating'] + self::withoutNulls([
            'ratingValue' => $value,
            'ratingCount' => $count,
            'reviewCount' => $reviewCount,
            'bestRating' => $best,
            'worstRating' => $worst,
        ]));
    }

    /**
     * Add a review; call once per review.
     */
    public function review(
        Person|Organization|string $author,
        float|int|string $rating,
        ?string $body = null,
        DateTimeInterface|string|null $publishedAt = null,
        float|int|null $best = null,
        float|int|null $worst = null,
    ): static {
        $this->reviews[] = ['@type' => 'Review'] + self::withoutNulls([
            'author' => self::agent($author),
            'reviewRating' => ['@type' => 'Rating'] + self::withoutNulls([
                'ratingValue' => $rating,
                'bestRating' => $best,
                'worstRating' => $worst,
            ]),
            'reviewBody' => $body,
            'datePublished' => $publishedAt,
        ]);

        return $this->set('review', $this->reviews);
    }
}

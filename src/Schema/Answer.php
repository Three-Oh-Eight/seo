<?php

namespace ThreeOhEight\Seo\Schema;

use DateTimeInterface;
use Laravel\Head\Schema\Organization;
use Laravel\Head\Schema\Person;
use Laravel\Head\Schema\SchemaObject;
use Laravel\Head\SchemaType;
use ThreeOhEight\Seo\Schema\Concerns\SetsFilledValues;

/**
 * An accepted or suggested answer to a Question. url should point at the
 * answer itself, eg with a fragment.
 */
#[SchemaType('Answer')]
class Answer extends SchemaObject
{
    use SetsFilledValues;

    public function text(?string $text): static
    {
        return $this->setFilled('text', $text);
    }

    public function author(Person|Organization|string|null $author): static
    {
        return $this->agentFilled('author', $author);
    }

    public function url(?string $url): static
    {
        return $this->setFilled('url', $url);
    }

    public function upvotes(?int $count): static
    {
        return $this->setFilled('upvoteCount', $count);
    }

    public function publishedAt(DateTimeInterface|string|null $date): static
    {
        return $this->dateFilled('datePublished', $date);
    }

    public function modifiedAt(DateTimeInterface|string|null $date): static
    {
        return $this->dateFilled('dateModified', $date);
    }
}

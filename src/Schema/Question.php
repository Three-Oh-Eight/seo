<?php

namespace ThreeOhEight\Seo\Schema;

use DateTimeInterface;
use Laravel\Head\Schema\Organization;
use Laravel\Head\Schema\Person;
use Laravel\Head\SchemaType;
use ThreeOhEight\Seo\Schema\Concerns\SetsFilledValues;

/**
 * The mainEntity of a QAPage. answerCount defaults to the number of accepted
 * and suggested answers added; set it when the page shows more answers than
 * the markup lists.
 */
#[SchemaType('Question')]
class Question extends LazySchemaObject
{
    use SetsFilledValues;

    /** @var list<Answer> */
    protected array $accepted = [];

    /** @var list<Answer> */
    protected array $suggested = [];

    public function name(?string $name): static
    {
        return $this->setFilled('name', $name);
    }

    public function text(?string $text): static
    {
        return $this->setFilled('text', $text);
    }

    public function author(Person|Organization|string|null $author): static
    {
        return $this->agentFilled('author', $author);
    }

    public function publishedAt(DateTimeInterface|string|null $date): static
    {
        return $this->dateFilled('datePublished', $date);
    }

    public function modifiedAt(DateTimeInterface|string|null $date): static
    {
        return $this->dateFilled('dateModified', $date);
    }

    public function upvotes(?int $count): static
    {
        return $this->setFilled('upvoteCount', $count);
    }

    public function answerCount(?int $count): static
    {
        return $this->setFilled('answerCount', $count);
    }

    public function acceptedAnswer(?Answer ...$answers): static
    {
        $this->accepted = [...$this->accepted, ...array_filter($answers)];

        return $this->setAnswers('acceptedAnswer', $this->accepted);
    }

    public function suggestedAnswer(?Answer ...$answers): static
    {
        $this->suggested = [...$this->suggested, ...array_filter($answers)];

        return $this->setAnswers('suggestedAnswer', $this->suggested);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return parent::toArray() + ['answerCount' => count($this->accepted) + count($this->suggested)];
    }

    /**
     * @param  list<Answer>  $answers
     */
    protected function setAnswers(string $property, array $answers): static
    {
        return match (count($answers)) {
            0 => $this,
            1 => $this->set($property, $answers[0]),
            default => $this->set($property, $answers),
        };
    }
}

<?php

namespace ThreeOhEight\Seo\Schema\Concerns;

use DateTimeInterface;
use Laravel\Head\Schema\Organization;
use Laravel\Head\Schema\Person;

/**
 * Lets typed setters take nullable model data straight from the database.
 * Head's schema validator rejects null and empty values outside production,
 * so empty input leaves the property out instead.
 */
trait SetsFilledValues
{
    protected function setFilled(string $property, mixed $value): static
    {
        if ($value === null || $value === '' || $value === []) {
            return $this;
        }

        return $this->set($property, $value);
    }

    protected function dateFilled(string $property, DateTimeInterface|string|null $date): static
    {
        return $date === null || $date === '' ? $this : $this->date($property, $date);
    }

    protected function agentFilled(string $property, Person|Organization|string|null $agent): static
    {
        return $agent === null || $agent === '' ? $this : $this->set($property, self::agent($agent));
    }

    protected static function agent(Person|Organization|string $agent): Person|Organization
    {
        return is_string($agent) ? (new Person)->name($agent) : $agent;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    protected static function withoutNulls(array $values): array
    {
        return array_filter($values, static fn (mixed $value): bool => $value !== null && $value !== '');
    }
}

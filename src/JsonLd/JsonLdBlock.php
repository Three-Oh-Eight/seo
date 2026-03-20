<?php

namespace ThreeOhEight\Seo\JsonLd;

class JsonLdBlock
{
    /** @var array<string, mixed> */
    private array $data = [];

    public function __construct(string $type)
    {
        $this->data['@type'] = $type;
    }

    public function title(string $title): self
    {
        $this->data['name'] = $title;

        return $this;
    }

    public function description(string $description): self
    {
        $this->data['description'] = $description;

        return $this;
    }

    public function value(string $key, mixed $value): self
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}

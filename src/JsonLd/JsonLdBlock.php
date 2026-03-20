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

    public static function make(string $type): self
    {
        return new self($type);
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
        return array_map(function (mixed $value) {
            if ($value instanceof self) {
                return $value->toArray();
            }

            if (is_array($value)) {
                return array_map(fn (mixed $item) => $item instanceof self ? $item->toArray() : $item, $value);
            }

            return $value;
        }, $this->data);
    }
}

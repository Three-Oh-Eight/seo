<?php

namespace ThreeOhEight\Seo\JsonLd;

class JsonLdCollection
{
    /** @var list<JsonLdBlock> */
    private array $blocks = [];

    public function add(JsonLdBlock $block): void
    {
        $this->blocks[] = $block;
    }

    public function isEmpty(): bool
    {
        return count($this->blocks) === 0;
    }

    public function render(): string
    {
        if ($this->isEmpty()) {
            return '';
        }

        $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

        if (count($this->blocks) === 1) {
            $data = ['@context' => 'https://schema.org', ...$this->blocks[0]->toArray()];

            return '<script type="application/ld+json">'.json_encode($data, $flags).'</script>';
        }

        $graph = array_map(fn (JsonLdBlock $block) => $block->toArray(), $this->blocks);
        $data = [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];

        return '<script type="application/ld+json">'.json_encode($data, $flags).'</script>';
    }
}

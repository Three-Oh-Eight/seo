<?php

namespace ThreeOhEight\Seo\JsonLd;

class JsonLdCollection
{
    /** @var list<JsonLdBlock> */
    private array $blocks = [];

    /** @var list<JsonLdBlock> */
    private array $separateBlocks = [];

    public function add(JsonLdBlock $block): void
    {
        $this->blocks[] = $block;
    }

    public function addSeparate(JsonLdBlock $block): void
    {
        $this->separateBlocks[] = $block;
    }

    public function isEmpty(): bool
    {
        return count($this->blocks) === 0 && count($this->separateBlocks) === 0;
    }

    public function render(): string
    {
        if ($this->isEmpty()) {
            return '';
        }

        $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        $parts = [];

        if (count($this->blocks) === 1) {
            $data = ['@context' => 'https://schema.org', ...$this->blocks[0]->toArray()];
            $parts[] = '<script type="application/ld+json">'.json_encode($data, $flags).'</script>';
        } elseif (count($this->blocks) > 1) {
            $graph = array_map(fn (JsonLdBlock $block) => $block->toArray(), $this->blocks);
            $data = [
                '@context' => 'https://schema.org',
                '@graph' => $graph,
            ];
            $parts[] = '<script type="application/ld+json">'.json_encode($data, $flags).'</script>';
        }

        foreach ($this->separateBlocks as $block) {
            $data = ['@context' => 'https://schema.org', ...$block->toArray()];
            $parts[] = '<script type="application/ld+json">'.json_encode($data, $flags).'</script>';
        }

        return implode("\n", $parts);
    }
}

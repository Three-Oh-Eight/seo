<?php

namespace ThreeOhEight\Seo\Tags;

use Laravel\Head\Rendering\ResolvedHead;
use Laravel\Head\Rendering\TagRenderer;
use Laravel\Head\Tags\OpenGraph;
use Laravel\Head\Tags\TagBuilder;

/**
 * Renders og:url from the resolved canonical URL whenever the page has Open
 * Graph data, which head itself only emits when og(url: ...) is set. An
 * explicit Head::og(url: ...) or withHead(og: ['url' => ...]) wins and is
 * rendered by head; withHead(extensions: ['ogUrl' => false]) turns it off
 * for a route, config seo.og_url for the whole site.
 *
 * @phpstan-consistent-constructor
 */
class OgUrl extends TagBuilder
{
    public function __construct(protected bool $disabled = false)
    {
        //
    }

    public static function key(): string
    {
        return 'ogUrl';
    }

    public static function fromRouteAttribute(string $key, mixed $value): ?self
    {
        return $value === false ? new self(disabled: true) : null;
    }

    public static function rendersWhenEmpty(ResolvedHead $head): bool
    {
        return true;
    }

    public function overlayOn(?TagBuilder $base): static
    {
        return $this;
    }

    public function isEmpty(): bool
    {
        return ! $this->disabled;
    }

    public function toHeadArray(ResolvedHead $head): ?string
    {
        return $this->resolve($head);
    }

    public function toTags(ResolvedHead $head, TagRenderer $tags): array
    {
        $url = $this->resolve($head);

        return $url ? [$tags->meta('property', 'og:url', $url, 'og:url')] : [];
    }

    protected function resolve(ResolvedHead $head): ?string
    {
        $openGraph = $head->builder(OpenGraph::class);

        if ($this->disabled || ! config('seo.og_url', true) || ! $openGraph instanceof OpenGraph) {
            return null;
        }

        // The same view head renders, including the title and description
        // fallbacks: no og tags means no og:url, an explicit url means head
        // already renders it.
        $rendered = $openGraph->toHeadArray($head);

        return $rendered === [] || isset($rendered['url']) ? null : $head->canonical();
    }
}

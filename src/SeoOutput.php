<?php

namespace ThreeOhEight\Seo;

use Illuminate\Contracts\Support\Htmlable;
use Stringable;

readonly class SeoOutput implements Htmlable, Stringable
{
    public function __construct(
        private string $html,
    ) {}

    public function toHtml(): string
    {
        return $this->html;
    }

    public function __toString(): string
    {
        return $this->html;
    }
}

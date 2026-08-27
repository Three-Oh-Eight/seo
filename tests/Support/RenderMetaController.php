<?php

namespace ThreeOhEight\Seo\Tests\Support;

use ThreeOhEight\Seo\Seo;

class RenderMetaController
{
    public function __invoke(): string
    {
        return app(Seo::class)->renderMeta()->toHtml();
    }
}

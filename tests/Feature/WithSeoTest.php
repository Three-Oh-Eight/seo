<?php

use ThreeOhEight\Seo\Concerns\HasSeo;
use ThreeOhEight\Seo\Concerns\WithSeo;
use ThreeOhEight\Seo\Contracts\Seoable;
use ThreeOhEight\Seo\Seo;

it('calls custom seo method when defined', function () {
    $component = new class
    {
        use WithSeo;

        public function seo(Seo $seo): void
        {
            $seo->title('From Method');
        }
    };

    $component->renderingWithSeo();

    $html = app(Seo::class)->renderMeta()->toHtml();

    expect($html)->toContain('From Method');
});

it('auto-detects public seoable properties', function () {
    $model = new class implements Seoable
    {
        use HasSeo;

        public ?string $meta_title = null;
        public ?string $title = 'Model Title';
        public ?string $meta_description = null;
        public ?string $description = null;
        public ?string $excerpt = null;
    };

    $component = new class
    {
        use WithSeo;

        public $post;
    };
    $component->post = $model;

    $component->renderingWithSeo();

    $html = app(Seo::class)->renderMeta()->toHtml();

    expect($html)->toContain('Model Title');
});

it('skips non-seoable properties', function () {
    $component = new class
    {
        use WithSeo;

        public string $query = 'test';
        public int $page = 1;
    };

    // Should not throw
    $component->renderingWithSeo();

    expect(true)->toBeTrue();
});

it('prefers seo method over auto-detection', function () {
    $model = new class implements Seoable
    {
        use HasSeo;

        public ?string $meta_title = null;
        public ?string $title = 'Model Title';
        public ?string $meta_description = null;
        public ?string $description = null;
        public ?string $excerpt = null;
    };

    $component = new class
    {
        use WithSeo;

        public $post;

        public function seo(Seo $seo): void
        {
            $seo->title('From Method Override');
        }
    };
    $component->post = $model;

    $component->renderingWithSeo();

    $html = app(Seo::class)->renderMeta()->toHtml();

    // Should use method, not model property
    expect($html)->toContain('From Method Override')
        ->and($html)->not->toContain('Model Title');
});

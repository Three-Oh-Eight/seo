<?php

use ThreeOhEight\Seo\Concerns\HasSeo;
use ThreeOhEight\Seo\Contracts\Seoable;
use ThreeOhEight\Seo\Seo;

it('sets title from meta_title', function () {
    $model = new class implements Seoable
    {
        use HasSeo;

        public ?string $meta_title = 'Meta Title';
        public ?string $title = 'Regular Title';
    };

    $seo = $this->makeSeo();
    $model->toSeo($seo);
    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('<title>Meta Title - TestSite</title>');
});

it('falls back to title when meta_title is null', function () {
    $model = new class implements Seoable
    {
        use HasSeo;

        public ?string $meta_title = null;
        public ?string $title = 'Regular Title';
    };

    $seo = $this->makeSeo();
    $model->toSeo($seo);
    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('<title>Regular Title - TestSite</title>');
});

it('sets description from meta_description', function () {
    $model = new class implements Seoable
    {
        use HasSeo;

        public ?string $meta_description = 'Meta Desc';
        public ?string $description = 'Regular Desc';
        public ?string $excerpt = 'Excerpt';
    };

    $seo = $this->makeSeo();
    $model->toSeo($seo);
    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('content="Meta Desc"');
});

it('falls back to description then excerpt', function () {
    $model = new class implements Seoable
    {
        use HasSeo;

        public ?string $meta_description = null;
        public ?string $description = null;
        public ?string $excerpt = 'Excerpt text';
    };

    $seo = $this->makeSeo();
    $model->toSeo($seo);
    $html = $seo->renderMeta()->toHtml();

    expect($html)->toContain('content="Excerpt text"');
});

it('skips title when all title properties are null', function () {
    $model = new class implements Seoable
    {
        use HasSeo;

        public ?string $meta_title = null;
        public ?string $title = null;
    };

    $seo = $this->makeSeo();
    $model->toSeo($seo);
    $html = $seo->renderMeta()->toHtml();

    // Should render site name only (no page title set)
    expect($html)->toContain('<title>TestSite</title>');
});

it('skips description when all description properties are null', function () {
    $model = new class implements Seoable
    {
        use HasSeo;

        public ?string $meta_description = null;
        public ?string $description = null;
        public ?string $excerpt = null;
    };

    $seo = $this->makeSeo();
    $model->toSeo($seo);
    $html = $seo->renderMeta()->toHtml();

    expect($html)->not->toContain('name="description"');
});

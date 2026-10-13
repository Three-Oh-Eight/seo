<?php

use Illuminate\Database\Eloquent\Model;
use Laravel\Head\Facades\Head;
use Laravel\Head\HeadManager;
use ThreeOhEight\Seo\Concerns\HasSeo;
use ThreeOhEight\Seo\Contracts\Seoable;

function seoablePost(array $attributes): Model&Seoable
{
    return new class($attributes) extends Model implements Seoable
    {
        use HasSeo;

        protected $guarded = [];
    };
}

it('applies title and description from a model via Head::from', function () {
    $post = seoablePost(['title' => 'Post title', 'excerpt' => 'Post excerpt']);

    $this->headRoute('/post', fn () => Head::from($post));

    $this->get('/post')
        ->assertSee('<title>Post title - TestSite</title>', false)
        ->assertSee('<meta name="description" content="Post excerpt">', false)
        ->assertSee('<meta property="og:description" content="Post excerpt">', false);
});

it('prefers meta_title and meta_description', function () {
    $post = seoablePost([
        'title' => 'Post title',
        'meta_title' => 'Meta title',
        'description' => 'Plain description',
        'meta_description' => 'Meta description',
    ]);

    $this->headRoute('/post', fn () => Head::from($post));

    $this->get('/post')
        ->assertSee('<title>Meta title - TestSite</title>', false)
        ->assertSee('<meta name="description" content="Meta description">', false)
        ->assertDontSee('Plain description', false);
});

it('keeps the defaults when the model has no seo attributes', function () {
    $post = seoablePost([]);

    $this->headRoute('/post', fn () => Head::from($post));

    $this->get('/post')
        ->assertSee('<title>TestSite</title>', false)
        ->assertSee('<meta name="description" content="Default description">', false);
});

it('falls back to the excerpt when meta_description is an empty string', function () {
    $post = seoablePost(['meta_description' => '', 'description' => '', 'excerpt' => 'Post excerpt']);

    $this->headRoute('/post', fn () => Head::from($post));

    $this->get('/post')
        ->assertSee('<meta name="description" content="Post excerpt">', false);
});

it('falls back to the title when meta_title is whitespace only', function () {
    $post = seoablePost(['meta_title' => "  \t ", 'title' => 'Post title']);

    $this->headRoute('/post', fn () => Head::from($post));

    $this->get('/post')
        ->assertSee('<title>Post title - TestSite</title>', false);
});

it('keeps the config defaults when every seo attribute is empty', function () {
    $post = seoablePost([
        'meta_title' => '',
        'title' => ' ',
        'meta_description' => '',
        'description' => '  ',
        'excerpt' => '',
    ]);

    $this->headRoute('/post', fn () => Head::from($post));

    $this->get('/post')
        ->assertSee('<title>TestSite</title>', false)
        ->assertSee('<meta name="description" content="Default description">', false);
});

it('overrides route metadata and returns the head manager for chaining', function () {
    $post = seoablePost(['title' => 'Post title']);

    $this->headRoute('/post', function () use ($post) {
        expect(Head::from($post))->toBeInstanceOf(HeadManager::class);
    })->withHead(title: 'Route title');

    $this->get('/post')->assertSee('<title>Post title - TestSite</title>', false);
});

it('supports a custom toSeo implementation', function () {
    $product = new class implements Seoable
    {
        public function toSeo(HeadManager $head): void
        {
            $head->title('Custom')->ogImage('https://cdn.test/product.png');
        }
    };

    $this->headRoute('/product', fn () => Head::from($product));

    $this->get('/product')
        ->assertSee('<title>Custom - TestSite</title>', false)
        ->assertSee('<meta property="og:image" content="https://cdn.test/product.png">', false);
});

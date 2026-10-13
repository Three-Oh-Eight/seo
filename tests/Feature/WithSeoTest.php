<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Laravel\Head\HeadManager;
use Livewire\Component;
use Livewire\Livewire;
use ThreeOhEight\Seo\Concerns\HasSeo;
use ThreeOhEight\Seo\Concerns\WithSeo;
use ThreeOhEight\Seo\Contracts\Seoable;

class SeoTestPost extends Model implements Seoable
{
    use HasSeo;

    protected $guarded = [];
}

class SeoTestShowPost extends Component
{
    use WithSeo;

    public SeoTestPost $post;

    public string $query = 'ignored';

    public function mount(): void
    {
        $this->post = new SeoTestPost(['title' => 'Livewire post', 'excerpt' => 'Livewire excerpt']);
    }

    public function render(): string
    {
        return '<div>{{ $post->title }}</div>';
    }
}

class SeoTestCustomPage extends Component
{
    use WithSeo;

    public SeoTestPost $post;

    public function mount(): void
    {
        $this->post = new SeoTestPost(['title' => 'Model title']);
    }

    public function seo(HeadManager $head): void
    {
        $head->title('From seo method')->hiddenFromRobots();
    }

    public function render(): string
    {
        return '<div>custom</div>';
    }
}

it('applies public Seoable properties when the component renders', function () {
    Livewire::test(SeoTestShowPost::class)->assertSee('Livewire post');

    $html = Blade::render('@head');

    expect($html)->toContain('<title>Livewire post - TestSite</title>')
        ->and($html)->toContain('<meta name="description" content="Livewire excerpt">');
});

it('prefers the component seo method over property detection', function () {
    Livewire::test(SeoTestCustomPage::class)->assertSee('custom');

    $html = Blade::render('@head');

    expect($html)->toContain('<title>From seo method - TestSite</title>')
        ->and($html)->toContain('<meta name="robots" content="none">')
        ->and($html)->not->toContain('Model title');
});

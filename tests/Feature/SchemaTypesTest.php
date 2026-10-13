<?php

use Carbon\CarbonImmutable;
use Laravel\Head\Facades\Head;
use Laravel\Head\Facades\Schema;
use Laravel\Head\Schema\Person;
use Laravel\Head\Schema\SchemaFactory;
use ThreeOhEight\Seo\Schema\Answer;
use ThreeOhEight\Seo\Schema\QaPage;
use ThreeOhEight\Seo\Schema\Question;
use ThreeOhEight\Seo\Schema\SoftwareApplication;
use ThreeOhEight\Seo\Schema\WebApplication;

it('registers the builders on head\'s schema factory', function () {
    expect(Schema::softwareApplication())->toBeInstanceOf(SoftwareApplication::class)
        ->and(Schema::webApplication())->toBeInstanceOf(WebApplication::class)
        ->and(Schema::qaPage())->toBeInstanceOf(QaPage::class)
        ->and(Schema::question())->toBeInstanceOf(Question::class)
        ->and(Schema::answer())->toBeInstanceOf(Answer::class)
        ->and(Schema::make('qaPage')->toArray()['@type'])->toBe('QAPage');
});

it('renders a SoftwareApplication through @head', function () {
    $this->headRoute('/app', fn () => Head::schema(fn (SchemaFactory $schema) => $schema->softwareApplication()
        ->name('Payflo')
        ->description('Invoicing for freelancers')
        ->url('https://payflo.test')
        ->applicationCategory('BusinessApplication')
        ->operatingSystem('Windows, macOS')
        ->softwareVersion('2.1')
        ->price(0, 'EUR')
        ->aggregateRating(4.6, count: 120, best: 5)
        ->review('Jane Doe', 5, body: 'Does the job.', publishedAt: CarbonImmutable::parse('2026-09-01T10:00:00+00:00'))
    ));

    expect(jsonLdBlocks($this->get('/app')->getContent()))->toBe([[
        '@context' => 'https://schema.org',
        '@type' => 'SoftwareApplication',
        'name' => 'Payflo',
        'description' => 'Invoicing for freelancers',
        'url' => 'https://payflo.test',
        'applicationCategory' => 'BusinessApplication',
        'operatingSystem' => 'Windows, macOS',
        'softwareVersion' => '2.1',
        'offers' => ['@type' => 'Offer', 'price' => 0, 'priceCurrency' => 'EUR'],
        'aggregateRating' => ['@type' => 'AggregateRating', 'ratingValue' => 4.6, 'ratingCount' => 120, 'bestRating' => 5],
        'review' => [[
            '@type' => 'Review',
            'author' => ['@type' => 'Person', 'name' => 'Jane Doe'],
            'reviewRating' => ['@type' => 'Rating', 'ratingValue' => 5],
            'reviewBody' => 'Does the job.',
            'datePublished' => '2026-09-01T10:00:00+00:00',
        ]],
    ]]);
});

it('renders the WebApplication variant with the same builder methods', function () {
    $this->headRoute('/app', fn () => Head::schema(fn (SchemaFactory $schema) => $schema->webApplication()
        ->name('Chaosdesk')
        ->applicationCategory('BusinessApplication')
        ->operatingSystem('Any')
        ->browserRequirements('Requires JavaScript.')
        ->price('9.95', 'EUR')
        ->aggregateRating('4.8', reviewCount: 31)
    ));

    $schema = jsonLdBlocks($this->get('/app')->getContent())[0];

    expect($schema['@type'])->toBe('WebApplication')
        ->and($schema['operatingSystem'])->toBe('Any')
        ->and($schema['browserRequirements'])->toBe('Requires JavaScript.')
        ->and($schema['offers'])->toBe(['@type' => 'Offer', 'price' => '9.95', 'priceCurrency' => 'EUR'])
        ->and($schema['aggregateRating'])->toBe(['@type' => 'AggregateRating', 'ratingValue' => '4.8', 'reviewCount' => 31]);
});

it('renders a QAPage with accepted and suggested answers', function () {
    $this->headRoute('/questions/1', fn () => Head::schema(fn (SchemaFactory $schema) => $schema->qaPage()->question(
        $schema->question()
            ->name('How do I reset my password?')
            ->text('The reset link never arrives.')
            ->author('Sam')
            ->publishedAt('2026-10-01T09:00:00+00:00')
            ->upvotes(4)
            ->acceptedAnswer(
                $schema->answer()
                    ->text('Check the spam folder.')
                    ->author((new Person)->name('Alex')->url('https://example.test/u/alex'))
                    ->url('https://example.test/questions/1#answer-7')
                    ->upvotes(12)
                    ->publishedAt('2026-10-01T10:00:00+00:00'),
            )
            ->suggestedAnswer(
                $schema->answer()->text('Use another address.')->upvotes(0),
                $schema->answer()->text('Contact support.'),
            )
    )));

    $page = jsonLdBlocks($this->get('/questions/1')->getContent())[0];
    $question = $page['mainEntity'];

    expect($page['@type'])->toBe('QAPage')
        ->and($question['@type'])->toBe('Question')
        ->and($question['name'])->toBe('How do I reset my password?')
        ->and($question['text'])->toBe('The reset link never arrives.')
        ->and($question['author'])->toBe(['@type' => 'Person', 'name' => 'Sam'])
        ->and($question['datePublished'])->toBe('2026-10-01T09:00:00+00:00')
        ->and($question['upvoteCount'])->toBe(4)
        ->and($question['answerCount'])->toBe(3)
        ->and($question['acceptedAnswer'])->toBe([
            '@type' => 'Answer',
            'text' => 'Check the spam folder.',
            'author' => ['@type' => 'Person', 'name' => 'Alex', 'url' => 'https://example.test/u/alex'],
            'url' => 'https://example.test/questions/1#answer-7',
            'upvoteCount' => 12,
            'datePublished' => '2026-10-01T10:00:00+00:00',
        ])
        ->and($question['suggestedAnswer'])->toHaveCount(2)
        ->and($question['suggestedAnswer'][0])->toBe(['@type' => 'Answer', 'text' => 'Use another address.', 'upvoteCount' => 0]);
});

it('picks up answers added to a question after it was passed to the page', function () {
    $question = Schema::question()->name('Why is the sky blue?')->text('Asking for a friend.');
    $page = Schema::qaPage()->question($question);

    $question->acceptedAnswer(Schema::answer()->text('Rayleigh scattering.'));
    $question->suggestedAnswer(Schema::answer()->text('Because of the ocean.'), Schema::answer()->text('Magic.'));

    $this->headRoute('/questions/2', fn () => Head::schema($page));

    $mainEntity = jsonLdBlocks($this->get('/questions/2')->assertOk()->getContent())[0]['mainEntity'];

    expect($mainEntity['acceptedAnswer'])->toBe(['@type' => 'Answer', 'text' => 'Rayleigh scattering.'])
        ->and($mainEntity['suggestedAnswer'])->toHaveCount(2)
        ->and($mainEntity['answerCount'])->toBe(3);
});

it('keeps an explicit answerCount', function () {
    $question = Schema::question()->name('Q')->answerCount(9)->acceptedAnswer(Schema::answer()->text('A'));

    expect($question->toArray()['answerCount'])->toBe(9);
});

it('omits null and empty values so head\'s validator accepts the schema', function () {
    $this->headRoute('/app', fn () => Head::schema(Schema::softwareApplication()
        ->name('Payflo')
        ->description(null)
        ->url('')
        ->image([])
        ->applicationCategory(null)
        ->operatingSystem(null)
        ->offers(null)
        ->price(null, 'EUR')
        ->aggregateRating(null, count: 3)
    )->schema(Schema::qaPage()->question(Schema::question()
        ->name('Q')
        ->text(null)
        ->author(null)
        ->publishedAt(null)
        ->upvotes(null)
        ->acceptedAnswer(null, Schema::answer()->text('A')->author('')->url(null)->modifiedAt(''))
        ->suggestedAnswer(null)
    )));

    [$app, $page] = jsonLdBlocks($this->get('/app')->assertOk()->getContent());

    expect($app)->toBe(['@context' => 'https://schema.org', '@type' => 'SoftwareApplication', 'name' => 'Payflo'])
        ->and($page['mainEntity'])->toBe([
            '@type' => 'Question',
            'name' => 'Q',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'A'],
            'answerCount' => 1,
        ]);
});

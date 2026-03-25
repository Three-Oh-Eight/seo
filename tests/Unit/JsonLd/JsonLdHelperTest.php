<?php

use ThreeOhEight\Seo\JsonLd\JsonLdHelper;
use ThreeOhEight\Seo\Seo;

it('returns JsonLdHelper when jsonLd called without type', function () {
    $seo = $this->makeSeo();

    expect($seo->jsonLd())->toBeInstanceOf(JsonLdHelper::class);
});

it('still returns JsonLdBlock when jsonLd called with type', function () {
    $seo = $this->makeSeo();
    $seo->jsonLd('Organization')->title('Acme');

    $html = $seo->renderJsonLd()->toHtml();

    expect($html)->toContain('"@type":"Organization"')
        ->and($html)->toContain('"name":"Acme"');
});

it('renders SoftwareApplication schema', function () {
    $seo = $this->makeSeo();
    $seo->jsonLd()->softwareApplication('PayFlo', version: '2.0', url: 'https://payflo.eu', applicationCategory: 'BusinessApplication');

    $html = $seo->renderJsonLd()->toHtml();
    $json = str_replace(['<script type="application/ld+json">', '</script>'], '', $html);
    $data = json_decode($json, true);

    expect($data['@type'])->toBe('SoftwareApplication')
        ->and($data['name'])->toBe('PayFlo')
        ->and($data['version'])->toBe('2.0')
        ->and($data['url'])->toBe('https://payflo.eu')
        ->and($data['applicationCategory'])->toBe('BusinessApplication');
});

it('renders SoftwareApplication with only name', function () {
    $seo = $this->makeSeo();
    $seo->jsonLd()->softwareApplication('PayFlo');

    $html = $seo->renderJsonLd()->toHtml();
    $json = str_replace(['<script type="application/ld+json">', '</script>'], '', $html);
    $data = json_decode($json, true);

    expect($data['@type'])->toBe('SoftwareApplication')
        ->and($data['name'])->toBe('PayFlo')
        ->and($data)->not->toHaveKey('version')
        ->and($data)->not->toHaveKey('url');
});

it('renders WebApplication schema', function () {
    $seo = $this->makeSeo();
    $seo->jsonLd()->webApplication('ChaosDesk', url: 'https://chaosdesk.eu', browserRequirements: 'Requires JavaScript');

    $html = $seo->renderJsonLd()->toHtml();
    $json = str_replace(['<script type="application/ld+json">', '</script>'], '', $html);
    $data = json_decode($json, true);

    expect($data['@type'])->toBe('WebApplication')
        ->and($data['name'])->toBe('ChaosDesk')
        ->and($data['url'])->toBe('https://chaosdesk.eu')
        ->and($data['browserRequirements'])->toBe('Requires JavaScript');
});

it('renders FAQPage schema with questions', function () {
    $seo = $this->makeSeo();
    $seo->jsonLd()->faqPage([
        ['q' => 'What is PayFlo?', 'a' => 'EU VAT compliance platform.'],
        ['q' => 'How much does it cost?', 'a' => 'Starting at 29/month.'],
    ]);

    $html = $seo->renderJsonLd()->toHtml();
    $json = str_replace(['<script type="application/ld+json">', '</script>'], '', $html);
    $data = json_decode($json, true);

    expect($data['@type'])->toBe('FAQPage')
        ->and($data['mainEntity'])->toHaveCount(2)
        ->and($data['mainEntity'][0]['@type'])->toBe('Question')
        ->and($data['mainEntity'][0]['name'])->toBe('What is PayFlo?')
        ->and($data['mainEntity'][0]['acceptedAnswer']['@type'])->toBe('Answer')
        ->and($data['mainEntity'][0]['acceptedAnswer']['text'])->toBe('EU VAT compliance platform.');
});

it('renders Organization schema', function () {
    $seo = $this->makeSeo();
    $seo->jsonLd()->organization('Three Oh Eight', url: 'https://308.nl', logo: 'https://308.nl/logo.png', description: 'Software company');

    $html = $seo->renderJsonLd()->toHtml();
    $json = str_replace(['<script type="application/ld+json">', '</script>'], '', $html);
    $data = json_decode($json, true);

    expect($data['@type'])->toBe('Organization')
        ->and($data['name'])->toBe('Three Oh Eight')
        ->and($data['url'])->toBe('https://308.nl')
        ->and($data['logo'])->toBe('https://308.nl/logo.png')
        ->and($data['description'])->toBe('Software company');
});

it('renders WebSite schema', function () {
    $seo = $this->makeSeo();
    $seo->jsonLd()->webSite('PayFlo', url: 'https://payflo.eu', description: 'VAT compliance');

    $html = $seo->renderJsonLd()->toHtml();
    $json = str_replace(['<script type="application/ld+json">', '</script>'], '', $html);
    $data = json_decode($json, true);

    expect($data['@type'])->toBe('WebSite')
        ->and($data['name'])->toBe('PayFlo')
        ->and($data['url'])->toBe('https://payflo.eu')
        ->and($data['description'])->toBe('VAT compliance');
});

it('chains helper methods back to Seo', function () {
    $seo = $this->makeSeo();

    $result = $seo->jsonLd()->softwareApplication('PayFlo');

    expect($result)->toBeInstanceOf(Seo::class);
});

it('combines helper and manual jsonLd blocks as graph', function () {
    $seo = $this->makeSeo();
    $seo->jsonLd()->organization('Three Oh Eight');
    $seo->jsonLd('WebSite')->title('PayFlo');

    $html = $seo->renderJsonLd()->toHtml();

    expect($html)->toContain('"@graph"');
});

// --- QAPage (separate script tags) ---

it('renders QAPage as separate script tag', function () {
    $seo = $this->makeSeo();
    $seo->jsonLd()->qaPage(
        question: 'What is VAT?',
        answer: 'A consumption tax.',
    );

    $html = $seo->renderJsonLd()->toHtml();
    $json = str_replace(['<script type="application/ld+json">', '</script>'], '', $html);
    $data = json_decode($json, true);

    expect($data['@type'])->toBe('QAPage')
        ->and($data['mainEntity']['@type'])->toBe('Question')
        ->and($data['mainEntity']['name'])->toBe('What is VAT?')
        ->and($data['mainEntity']['answerCount'])->toBe(1)
        ->and($data['mainEntity']['acceptedAnswer']['@type'])->toBe('Answer')
        ->and($data['mainEntity']['acceptedAnswer']['text'])->toBe('A consumption tax.');
});

it('renders QAPage with full details matching ikformeer pattern', function () {
    $seo = $this->makeSeo();
    $author = \ThreeOhEight\Seo\JsonLd\JsonLdBlock::make('Organization')
        ->title('VVD')
        ->value('alternateName', 'VVD')
        ->value('url', 'https://example.com/partij/vvd');

    $seo->jsonLd()->qaPage(
        question: 'Should taxes be lowered?',
        answer: 'Yes, we believe in lower taxes.',
        questionDetail: 'This statement addresses fiscal policy.',
        answerUrl: 'https://example.com/thema/fiscaal#answer-1-vvd',
        dateCreated: '2026-03-20T00:00:00+00:00',
        author: $author,
    );

    $html = $seo->renderJsonLd()->toHtml();
    $json = str_replace(['<script type="application/ld+json">', '</script>'], '', $html);
    $data = json_decode($json, true);

    expect($data['mainEntity']['text'])->toBe('This statement addresses fiscal policy.')
        ->and($data['mainEntity']['dateCreated'])->toBe('2026-03-20T00:00:00+00:00')
        ->and($data['mainEntity']['author']['@type'])->toBe('Organization')
        ->and($data['mainEntity']['author']['name'])->toBe('VVD')
        ->and($data['mainEntity']['acceptedAnswer']['url'])->toContain('#answer-1-vvd')
        ->and($data['mainEntity']['acceptedAnswer']['dateCreated'])->toBe('2026-03-20T00:00:00+00:00')
        ->and($data['mainEntity']['acceptedAnswer']['author']['name'])->toBe('VVD');
});

it('renders multiple QAPages as separate script tags not in graph', function () {
    $seo = $this->makeSeo();
    $seo->jsonLd()->qaPage('Q1?', 'A1.');
    $seo->jsonLd()->qaPage('Q2?', 'A2.');

    $html = $seo->renderJsonLd()->toHtml();
    $scriptCount = substr_count($html, '<script type="application/ld+json">');

    expect($scriptCount)->toBe(2)
        ->and($html)->not->toContain('"@graph"');
});

it('renders QAPages separate from regular blocks', function () {
    $seo = $this->makeSeo();
    $seo->jsonLd()->organization('Acme');
    $seo->jsonLd()->qaPage('Q1?', 'A1.');

    $html = $seo->renderJsonLd()->toHtml();
    $scriptCount = substr_count($html, '<script type="application/ld+json">');

    // Organization as regular block, QAPage as separate
    expect($scriptCount)->toBe(2);
});

// --- FAQPage with author ---

it('renders FAQPage with author string', function () {
    $seo = $this->makeSeo();
    $seo->jsonLd()->faqPage([
        ['q' => 'What is VAT?', 'a' => 'A tax.', 'author' => 'VVD'],
    ]);

    $html = $seo->renderJsonLd()->toHtml();
    $json = str_replace(['<script type="application/ld+json">', '</script>'], '', $html);
    $data = json_decode($json, true);

    expect($data['mainEntity'][0]['acceptedAnswer']['author']['@type'])->toBe('Organization')
        ->and($data['mainEntity'][0]['acceptedAnswer']['author']['name'])->toBe('VVD');
});

it('renders FAQPage with author as JsonLdBlock', function () {
    $seo = $this->makeSeo();
    $author = \ThreeOhEight\Seo\JsonLd\JsonLdBlock::make('PoliticalParty')->title('VVD');

    $seo->jsonLd()->faqPage([
        ['q' => 'What is VAT?', 'a' => 'A tax.', 'author' => $author],
    ]);

    $html = $seo->renderJsonLd()->toHtml();
    $json = str_replace(['<script type="application/ld+json">', '</script>'], '', $html);
    $data = json_decode($json, true);

    expect($data['mainEntity'][0]['acceptedAnswer']['author']['@type'])->toBe('PoliticalParty')
        ->and($data['mainEntity'][0]['acceptedAnswer']['author']['name'])->toBe('VVD');
});

it('renders FAQPage without author (backward compat)', function () {
    $seo = $this->makeSeo();
    $seo->jsonLd()->faqPage([
        ['q' => 'What?', 'a' => 'This.'],
    ]);

    $html = $seo->renderJsonLd()->toHtml();
    $json = str_replace(['<script type="application/ld+json">', '</script>'], '', $html);
    $data = json_decode($json, true);

    expect($data['mainEntity'][0]['acceptedAnswer'])->not->toHaveKey('author');
});

// --- Article ---

it('renders Article schema with all properties', function () {
    $seo = $this->makeSeo();
    $author = \ThreeOhEight\Seo\JsonLd\JsonLdBlock::make('Organization')->title('ikformeer.nl');
    $publisher = \ThreeOhEight\Seo\JsonLd\JsonLdBlock::make('Organization')
        ->title('ikformeer.nl')
        ->value('logo', \ThreeOhEight\Seo\JsonLd\JsonLdBlock::make('ImageObject')
            ->value('url', 'https://ikformeer.nl/logo.png')
            ->value('width', 512)
            ->value('height', 512));

    $seo->jsonLd()->article(
        headline: 'New Election Results',
        description: 'The latest poll results.',
        datePublished: '2026-03-20',
        dateModified: '2026-03-21',
        image: 'https://ikformeer.nl/og.png',
        author: $author,
        publisher: $publisher,
        articleSection: 'Politiek',
        keywords: 'elections,polls',
        url: 'https://ikformeer.nl/blog/results',
        inLanguage: 'nl-NL',
    );

    $html = $seo->renderJsonLd()->toHtml();
    $json = str_replace(['<script type="application/ld+json">', '</script>'], '', $html);
    $data = json_decode($json, true);

    expect($data['@type'])->toBe('Article')
        ->and($data['headline'])->toBe('New Election Results')
        ->and($data['description'])->toBe('The latest poll results.')
        ->and($data['datePublished'])->toBe('2026-03-20')
        ->and($data['dateModified'])->toBe('2026-03-21')
        ->and($data['image'])->toBe('https://ikformeer.nl/og.png')
        ->and($data['author']['name'])->toBe('ikformeer.nl')
        ->and($data['publisher']['logo']['@type'])->toBe('ImageObject')
        ->and($data['articleSection'])->toBe('Politiek')
        ->and($data['keywords'])->toBe('elections,polls')
        ->and($data['mainEntityOfPage']['@type'])->toBe('WebPage')
        ->and($data['mainEntityOfPage']['@id'])->toBe('https://ikformeer.nl/blog/results')
        ->and($data['inLanguage'])->toBe('nl-NL');
});

it('renders Article with only headline', function () {
    $seo = $this->makeSeo();
    $seo->jsonLd()->article('Simple Post');

    $html = $seo->renderJsonLd()->toHtml();
    $json = str_replace(['<script type="application/ld+json">', '</script>'], '', $html);
    $data = json_decode($json, true);

    expect($data['@type'])->toBe('Article')
        ->and($data['headline'])->toBe('Simple Post')
        ->and($data)->not->toHaveKey('datePublished');
});

// --- Enriched WebSite ---

it('renders WebSite with inLanguage and publisher', function () {
    $seo = $this->makeSeo();
    $publisher = \ThreeOhEight\Seo\JsonLd\JsonLdBlock::make('Organization')->title('Downsized');
    $searchAction = \ThreeOhEight\Seo\JsonLd\JsonLdBlock::make('SearchAction')
        ->value('target', 'https://ikformeer.nl/?q={search_term}')
        ->value('query-input', 'required name=search_term');

    $seo->jsonLd()->webSite(
        name: 'ikformeer.nl',
        url: 'https://ikformeer.nl',
        description: 'Coalitievormer',
        inLanguage: 'nl-NL',
        publisher: $publisher,
        potentialAction: $searchAction,
    );

    $html = $seo->renderJsonLd()->toHtml();
    $json = str_replace(['<script type="application/ld+json">', '</script>'], '', $html);
    $data = json_decode($json, true);

    expect($data['@type'])->toBe('WebSite')
        ->and($data['inLanguage'])->toBe('nl-NL')
        ->and($data['publisher']['@type'])->toBe('Organization')
        ->and($data['potentialAction']['@type'])->toBe('SearchAction')
        ->and($data['potentialAction']['target'])->toContain('{search_term}');
});

it('renders WebSite with only name (backward compat)', function () {
    $seo = $this->makeSeo();
    $seo->jsonLd()->webSite('PayFlo');

    $html = $seo->renderJsonLd()->toHtml();
    $json = str_replace(['<script type="application/ld+json">', '</script>'], '', $html);
    $data = json_decode($json, true);

    expect($data['@type'])->toBe('WebSite')
        ->and($data['name'])->toBe('PayFlo')
        ->and($data)->not->toHaveKey('inLanguage')
        ->and($data)->not->toHaveKey('publisher');
});

// --- Organization with ImageObject logo ---

it('renders Organization with string logo (backward compat)', function () {
    $seo = $this->makeSeo();
    $seo->jsonLd()->organization('Acme', logo: 'https://acme.com/logo.png');

    $html = $seo->renderJsonLd()->toHtml();
    $json = str_replace(['<script type="application/ld+json">', '</script>'], '', $html);
    $data = json_decode($json, true);

    expect($data['logo'])->toBe('https://acme.com/logo.png');
});

it('renders Organization with ImageObject logo', function () {
    $seo = $this->makeSeo();
    $logo = \ThreeOhEight\Seo\JsonLd\JsonLdBlock::make('ImageObject')
        ->value('url', 'https://ikformeer.nl/favicon-512x512.png')
        ->value('width', 512)
        ->value('height', 512);

    $seo->jsonLd()->organization('Downsized', url: 'https://downsized.nl', logo: $logo);

    $html = $seo->renderJsonLd()->toHtml();
    $json = str_replace(['<script type="application/ld+json">', '</script>'], '', $html);
    $data = json_decode($json, true);

    expect($data['logo']['@type'])->toBe('ImageObject')
        ->and($data['logo']['url'])->toBe('https://ikformeer.nl/favicon-512x512.png')
        ->and($data['logo']['width'])->toBe(512)
        ->and($data['logo']['height'])->toBe(512);
});

// --- jsonLdSeparate direct API ---

it('renders jsonLdSeparate as individual script tag', function () {
    $seo = $this->makeSeo();
    $seo->jsonLdSeparate('QAPage')->value('mainEntity', 'test');

    $html = $seo->renderJsonLd()->toHtml();

    expect($html)->toContain('"@type":"QAPage"')
        ->and($html)->not->toContain('"@graph"');
});

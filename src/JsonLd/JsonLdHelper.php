<?php

namespace ThreeOhEight\Seo\JsonLd;

use ThreeOhEight\Seo\Seo;

class JsonLdHelper
{
    public function __construct(
        private JsonLdCollection $collection,
        private Seo $seo,
    ) {}

    public function softwareApplication(
        string $name,
        ?string $version = null,
        ?string $url = null,
        ?string $operatingSystem = null,
        ?string $applicationCategory = null,
    ): Seo {
        $block = JsonLdBlock::make('SoftwareApplication')->title($name);

        if ($version !== null) {
            $block->value('version', $version);
        }

        if ($url !== null) {
            $block->value('url', $url);
        }

        if ($operatingSystem !== null) {
            $block->value('operatingSystem', $operatingSystem);
        }

        if ($applicationCategory !== null) {
            $block->value('applicationCategory', $applicationCategory);
        }

        $this->collection->add($block);

        return $this->seo;
    }

    public function webApplication(
        string $name,
        ?string $url = null,
        ?string $applicationCategory = null,
        ?string $browserRequirements = null,
    ): Seo {
        $block = JsonLdBlock::make('WebApplication')->title($name);

        if ($url !== null) {
            $block->value('url', $url);
        }

        if ($applicationCategory !== null) {
            $block->value('applicationCategory', $applicationCategory);
        }

        if ($browserRequirements !== null) {
            $block->value('browserRequirements', $browserRequirements);
        }

        $this->collection->add($block);

        return $this->seo;
    }

    /**
     * @param  list<array{q: string, a: string, author?: string|JsonLdBlock}>  $questions
     */
    public function faqPage(array $questions): Seo
    {
        $block = JsonLdBlock::make('FAQPage');
        $mainEntity = [];

        foreach ($questions as $qa) {
            $answer = JsonLdBlock::make('Answer')->value('text', $qa['a']);

            if (isset($qa['author'])) {
                $author = $qa['author'] instanceof JsonLdBlock
                    ? $qa['author']
                    : JsonLdBlock::make('Organization')->title($qa['author']);

                $answer->value('author', $author);
            }

            $question = JsonLdBlock::make('Question')
                ->title($qa['q'])
                ->value('acceptedAnswer', $answer);

            $mainEntity[] = $question;
        }

        $block->value('mainEntity', $mainEntity);
        $this->collection->add($block);

        return $this->seo;
    }

    /**
     * Creates a QAPage schema rendered as a separate <script> tag (not merged into @graph).
     * Matches the Google-validated pattern used by ikformeer.nl.
     */
    public function qaPage(
        string $question,
        string $answer,
        ?string $questionDetail = null,
        ?string $answerUrl = null,
        ?string $dateCreated = null,
        JsonLdBlock|null $author = null,
    ): Seo {
        $answerBlock = JsonLdBlock::make('Answer')->value('text', $answer);

        if ($answerUrl !== null) {
            $answerBlock->value('url', $answerUrl);
        }

        if ($dateCreated !== null) {
            $answerBlock->value('dateCreated', $dateCreated);
        }

        if ($author !== null) {
            $answerBlock->value('author', $author);
        }

        $questionBlock = JsonLdBlock::make('Question')
            ->title($question)
            ->value('answerCount', 1)
            ->value('acceptedAnswer', $answerBlock);

        if ($questionDetail !== null) {
            $questionBlock->value('text', $questionDetail);
        }

        if ($dateCreated !== null) {
            $questionBlock->value('dateCreated', $dateCreated);
        }

        if ($author !== null) {
            $questionBlock->value('author', $author);
        }

        $page = JsonLdBlock::make('QAPage')->value('mainEntity', $questionBlock);
        $this->collection->addSeparate($page);

        return $this->seo;
    }

    public function article(
        string $headline,
        ?string $description = null,
        ?string $datePublished = null,
        ?string $dateModified = null,
        ?string $image = null,
        JsonLdBlock|null $author = null,
        JsonLdBlock|null $publisher = null,
        ?string $articleSection = null,
        ?string $keywords = null,
        ?string $url = null,
        ?string $inLanguage = null,
    ): Seo {
        $block = JsonLdBlock::make('Article')->value('headline', $headline);

        if ($description !== null) {
            $block->description($description);
        }

        if ($datePublished !== null) {
            $block->value('datePublished', $datePublished);
        }

        if ($dateModified !== null) {
            $block->value('dateModified', $dateModified);
        }

        if ($image !== null) {
            $block->value('image', $image);
        }

        if ($author !== null) {
            $block->value('author', $author);
        }

        if ($publisher !== null) {
            $block->value('publisher', $publisher);
        }

        if ($articleSection !== null) {
            $block->value('articleSection', $articleSection);
        }

        if ($keywords !== null) {
            $block->value('keywords', $keywords);
        }

        if ($url !== null) {
            $block->value('mainEntityOfPage', JsonLdBlock::make('WebPage')->value('@id', $url));
        }

        if ($inLanguage !== null) {
            $block->value('inLanguage', $inLanguage);
        }

        $this->collection->add($block);

        return $this->seo;
    }

    public function organization(
        string $name,
        ?string $url = null,
        string|JsonLdBlock|null $logo = null,
        ?string $description = null,
    ): Seo {
        $block = JsonLdBlock::make('Organization')->title($name);

        if ($url !== null) {
            $block->value('url', $url);
        }

        if ($logo !== null) {
            $block->value('logo', $logo);
        }

        if ($description !== null) {
            $block->description($description);
        }

        $this->collection->add($block);

        return $this->seo;
    }

    public function webSite(
        string $name,
        ?string $url = null,
        ?string $description = null,
        ?string $inLanguage = null,
        JsonLdBlock|null $publisher = null,
        JsonLdBlock|null $potentialAction = null,
    ): Seo {
        $block = JsonLdBlock::make('WebSite')->title($name);

        if ($url !== null) {
            $block->value('url', $url);
        }

        if ($description !== null) {
            $block->description($description);
        }

        if ($inLanguage !== null) {
            $block->value('inLanguage', $inLanguage);
        }

        if ($publisher !== null) {
            $block->value('publisher', $publisher);
        }

        if ($potentialAction !== null) {
            $block->value('potentialAction', $potentialAction);
        }

        $this->collection->add($block);

        return $this->seo;
    }
}

<?php

namespace ThreeOhEight\Seo\Schema;

use Laravel\Head\SchemaType;

/**
 * Google's Q&A rich result: a page about one question and its answers, as
 * opposed to a FAQ written by the site itself.
 */
#[SchemaType('QAPage')]
class QaPage extends LazySchemaObject
{
    public function question(Question $question): static
    {
        return $this->set('mainEntity', $question);
    }
}

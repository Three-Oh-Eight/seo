<?php

namespace ThreeOhEight\Seo\Schema;

use Laravel\Head\SchemaType;

/**
 * The schema.org subtype for applications that run in the browser. Google
 * treats it as a SoftwareApplication, so the same properties apply.
 */
#[SchemaType('WebApplication')]
class WebApplication extends SoftwareApplication
{
    /**
     * Eg "Requires JavaScript. Requires HTML5."
     */
    public function browserRequirements(?string $requirements): static
    {
        return $this->setFilled('browserRequirements', $requirements);
    }
}

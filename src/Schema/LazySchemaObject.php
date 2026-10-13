<?php

namespace ThreeOhEight\Seo\Schema;

use Laravel\Head\Schema\SchemaObject;

/**
 * A schema object that keeps nested schema objects as objects until it is
 * serialised. Head's SchemaObject flattens them to arrays when they are set,
 * so anything added to a nested object afterwards is lost; resolving at
 * toArray() time keeps a shared, still-growing object in sync.
 */
abstract class LazySchemaObject extends SchemaObject
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['@type' => $this->type] + array_map($this->resolve(...), $this->properties);
    }

    protected function coerce(mixed $value): mixed
    {
        if ($value instanceof SchemaObject) {
            return $value;
        }

        if (is_array($value)) {
            return array_map($this->coerce(...), $value);
        }

        return parent::coerce($value);
    }

    protected function resolve(mixed $value): mixed
    {
        if ($value instanceof SchemaObject) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return array_map($this->resolve(...), $value);
        }

        return $value;
    }
}

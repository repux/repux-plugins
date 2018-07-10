<?php

namespace App\DataFixtures;

trait ReferenceHelperTrait
{
    public static function buildReferenceName(string $prefix, string $name): string
    {
        return sprintf('%s:%s', $prefix, $name);
    }

    public static function getReferenceName(...$names): string
    {
        if (!defined('static::REFERENCE_PREFIX') || empty(static::REFERENCE_PREFIX)) {
            throw new \InvalidArgumentException(sprintf('Const %s::REFERENCE_PREFIX cannot be empty.', static::class));
        }

        $name = join(':', $names);

        return static::buildReferenceName(static::REFERENCE_PREFIX, $name);
    }
}

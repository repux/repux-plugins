<?php

namespace App\Repository\Traits;

trait RepositoryAliasTrait
{
    public static function getFieldName(string $alias, string $fieldName): string
    {
        return sprintf('%s.%s', $alias, $fieldName);
    }

    public static function getAliasedFieldName(string $fieldName): string
    {
        if (!defined('static::ALIAS') || empty(static::ALIAS)) {
            throw new \InvalidArgumentException(sprintf('Const %s::ALIAS cannot be empty.', static::class));
        }

        return static::getFieldName(static::ALIAS, $fieldName);
    }
}

<?php

namespace App\Request\ParamsHandler\Traits;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

trait PaginableTrait
{
    public function getPage(): int
    {
        return $this->getOption('page');
    }

    public function getLimit(): int
    {
        return $this->getOption('limit');
    }

    public function configurePaginable(OptionsResolver $optionsResolver)
    {
        $optionsResolver
            ->setRequired(['page', 'limit'])
            ->setDefaults(['page' => 1, 'limit' => 25])
            ->setAllowedTypes('page', 'numeric')
            ->setAllowedTypes('limit', 'numeric')
            ->setNormalizer('page', function (Options $options, $value) {
                return max(1, $value);
            })
            ->setNormalizer('limit', function (Options $options, $value) {
                return max(1, $value);
            });
    }
}

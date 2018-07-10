<?php

namespace App\Request\ParamsHandler\Traits;

use App\Request\ParamsHandler\AbstractParamsHandler;
use Symfony\Component\OptionsResolver\OptionsResolver;

trait SortableTrait
{
    public function getSortBy(): string
    {
        return $this->getOption('sortby');
    }

    public function getSortDirection(): string
    {
        return $this->getOption('sortdir');
    }

    public function configureSortable(
        OptionsResolver $optionsResolver,
        array $allowedSortBy = [],
        string $defaultSortBy = '',
        string $defaultSortDir = AbstractParamsHandler::SORT_ASC
    ) {

        $defaultSortDir = strcasecmp($defaultSortDir, AbstractParamsHandler::SORT_DESC) === 0
            ? AbstractParamsHandler::SORT_DESC
            : AbstractParamsHandler::SORT_ASC;

        $defaultSortBy = isset($allowedSortBy[$defaultSortBy]) ? $defaultSortBy : '';
        $allowedSortBy[''] = '';

        $optionsResolver
            ->setRequired(['sortby', 'sortdir'])
            ->setDefaults(['sortby' => $defaultSortBy, 'sortdir' => $defaultSortDir])
            ->setAllowedValues('sortdir', [AbstractParamsHandler::SORT_ASC, AbstractParamsHandler::SORT_DESC])
            ->setAllowedValues('sortby', array_keys($allowedSortBy))
            ->setAllowedTypes('sortby', 'string')
            ->setAllowedTypes('sortdir', 'string')
            ->setNormalizer('sortby', function ($options, $value) use ($allowedSortBy) {
                return $allowedSortBy[$value];
            });
    }
}

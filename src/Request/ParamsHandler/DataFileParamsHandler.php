<?php

namespace App\Request\ParamsHandler;

use App\Repository\DataFileRepository;
use App\Request\ParamsHandler\Traits\PaginableTrait;
use App\Request\ParamsHandler\Traits\SortableTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DataFileParamsHandler extends AbstractParamsHandler
{
    use PaginableTrait, SortableTrait;

    protected $allowedSortBy = [];

    public function __construct(Request $request)
    {
        $this->allowedSortBy = [
            'created_at' => DataFileRepository::getAliasedFieldName('createdAt'),
        ];

        parent::__construct($request);
    }

    /**
     * @inheritdoc
     */
    protected function configure(OptionsResolver $optionsResolver)
    {
        $this->configurePaginable($optionsResolver);
        $this->configureSortable(
            $optionsResolver,
            $this->allowedSortBy,
            'created_at',
            AbstractParamsHandler::SORT_DESC
        );
    }
}

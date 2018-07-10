<?php

namespace App\Request\ParamsHandler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractParamsHandler
{
    const SORT_ASC = 'asc';
    const SORT_DESC = 'desc';

    private $options;

    public function __construct(Request $request)
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefined(['_format']);

        $this->configure($optionsResolver);

        $this->options = $optionsResolver->resolve($request->query->all());
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOption(string $option)
    {
        return $this->options[$option];
    }

    abstract protected function configure(OptionsResolver $optionsResolver);
}

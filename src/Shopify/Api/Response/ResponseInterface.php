<?php

namespace App\Shopify\Api\Response;

use Psr\Http\Message\ResponseInterface as PsrResponse;

interface ResponseInterface
{
    public function successful(): bool;

    /**
     * Get the body of the response.
     * If item is specified, this can be used to drill down into the response object and retrieve specific items within it
     * @param string $item
     * @param mixed $default
     * @return mixed
     */
    public function get($item = null, $default = null);

    public function getHttpResponse(): PsrResponse;
}

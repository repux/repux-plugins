<?php

namespace App\Shopify\Api\Response;

use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface as PsrResponse;

class ErrorResponse implements ResponseInterface
{
    private $response;

    private $exception;

    public function __construct(PsrResponse $response, ClientException $exception)
    {
        $this->response  = $response;
        $this->exception = $exception;
    }

    public function successful(): bool
    {
        return false;
    }

    /**
     * @param null $item
     * @param null $default
     * @return mixed
     */
    public function get($item = null, $default = null)
    {
        return 'An error occurred while processing the request.';
    }

    public function getHttpResponse(): PsrResponse
    {
        return $this->response;
    }
}

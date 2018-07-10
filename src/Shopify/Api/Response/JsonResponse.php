<?php

namespace App\Shopify\Api\Response;

use Psr\Http\Message\ResponseInterface as PsrResponse;

class JsonResponse implements ResponseInterface
{
    /**
     * @var array
     */
    private $decoded = null;

    private $response;

    public function __construct(PsrResponse $response)
    {
        $this->response = $response;
    }

    public function successful(): bool
    {
        return preg_match('/^2[\d]{2,}/', $this->getHttpResponse()->getStatusCode());
    }

    /**
     * Access elements of the JSON response using dot notation
     * @param null $item
     * @param null $default
     * @return mixed
     */
    public function get($item = null, $default = null)
    {
        if (is_null($item)) {
            return $default;
        }

        $decoded = $this->getDecodedJson();

        if (array_key_exists($item, $decoded)) {
            return $decoded[$item];
        }

        foreach (explode('.', $item) as $segment) {
            if (! is_array($decoded) || ! array_key_exists($segment, $decoded)) {
                return $default;
            }

            $decoded = $decoded[$segment];
        }

        return $decoded;
    }

    public function getHttpResponse(): PsrResponse
    {
        return $this->response;
    }

    private function getDecodedJson():array
    {
        if (!is_null($this->decoded)) {
            return $this->decoded;
        }

        try {
            return $this->decoded = \GuzzleHttp\json_decode(
                (string) $this->getHttpResponse()->getBody(),
                true
            );
        } catch (\InvalidArgumentException $e) {
            return $this->decoded = [];
        }
    }
}

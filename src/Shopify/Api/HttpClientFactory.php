<?php

namespace App\Shopify\Api;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class HttpClientFactory implements HttpClientFactoryInterface
{
    public function createHttpClient(string $storeName, PublicAppCredentials $credentials): ClientInterface
    {
        $handlers = HandlerStack::create();
        $handlers->push(Middleware::retry($this->newRetryDecider(), $this->getRetryDelay()));
        $handlers->push(Middleware::mapResponse($this->handleRateLimiter()));

        $options = [
            'base_uri' => sprintf('https://%s.myshopify.com', $storeName),
            'handler' => $handlers,
        ];

        switch (true) {
            case $credentials instanceof PublicAppCredentials:
                $options['headers'] = [
                    'X-Shopify-Access-Token' => $credentials->getAccessToken(),
                ];
                break;
            default:
                throw new \RuntimeException('Invalid credentials given');
        }

        return new Client($options);
    }

    private function newRetryDecider(): callable
    {
        return function (
            $retries,
            RequestInterface $request,
            ResponseInterface $response = null,
            \Exception $exception = null
        ) {
            if ($retries >= 5) {
                return false;
            }

            $shouldRetry = false;

            if ($exception instanceof ConnectException) {
                $shouldRetry = true;
            }

            if (!empty($response) && $response->getStatusCode() === Response::HTTP_TOO_MANY_REQUESTS) {
                $shouldRetry = true;
            }

            return $shouldRetry;
        };
    }

    private function getRetryDelay(): callable
    {
        return function ($retries, ResponseInterface $response) {
            $delay = 1000 * $retries;

            if (!$response->hasHeader('Retry-After')) {
                return $delay;
            }

            return (float)$response->getHeaderLine('Retry-After') * $delay;
        };
    }

    private function handleRateLimiter(): callable
    {
        return function (ResponseInterface $response) {
            $rateLimit = explode('/', $response->getHeaderLine('X-Shopify-Shop-Api-Call-Limit'));

            $volume = intval($rateLimit[0], 10);
            $limit = intval($rateLimit[1], 10);
            $limitPercentage = ($volume / $limit) * 100;

            if ($limitPercentage > 65) {
                sleep(2);
            }

            return $response;
        };
    }
}

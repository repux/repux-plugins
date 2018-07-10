<?php

namespace App\Shopify\Api\Endpoint;

use App\Shopify\Api\GenericResource;
use App\Shopify\Api\Request\Exception\FailedRequestException;
use App\Shopify\Api\Response\ErrorResponse;
use App\Shopify\Api\Response\JsonResponse;
use App\Shopify\Api\Response\ResponseInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;

abstract class AbstractEndpoint
{
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @throws FailedRequestException
     */
    protected function send(RequestInterface $request): ResponseInterface
    {
        $response = $this->process($request);

        if (!$response->successful()) {
            throw new FailedRequestException('Failed request. ' . $response->getHttpResponse()->getReasonPhrase());
        }

        return $response;
    }

    /**
     * @throws FailedRequestException
     */
    protected function sendPaged(RequestInterface $request, string $rootElement): array
    {
        return $this->processPaged($request, $rootElement);
    }

    /**
     * @param array $items
     * @param GenericResource|null $prototype
     * @return array
     */
    protected function createCollection(array $items, GenericResource $prototype = null): array
    {
        if (!$prototype) {
            $prototype = new GenericResource();
        }

        $collection = [];

        foreach ((array)$items as $item) {
            $newItem = clone $prototype;
            $newItem->hydrate($item);
            $collection[] = $newItem;
        }

        return $collection;
    }

    protected function createEntity(array $data): GenericResource
    {
        $entity = new GenericResource();
        $entity->hydrate($data);

        return $entity;
    }

    protected function process(RequestInterface $request): ResponseInterface
    {
        $guzzleResponse = $this->client->send($request);

        try {
            switch ($request->getHeaderLine('Content-type')) {
                case 'application/json':
                    $response = new JsonResponse($guzzleResponse);
                    break;
                default:
                    throw new \RuntimeException('Unsupported Content-type');
            }
        } catch (ClientException $e) {
            $response = new ErrorResponse($guzzleResponse, $e);
        }

        return $response;
    }

    /**
     * Loop through a set of API results that are available in pages, returning the full resultset as one array
     */
    protected function processPaged(RequestInterface $request, string $rootElement, array $params = []): array
    {
        if (empty($params['page'])) {
            $params['page'] = 1;
        }

        if (empty($params['limit'])) {
            $params['limit'] = 250;
        }

        $allResults = [];

        do {
            $requestUrl = $request->getUri();
            $paramSeparator = strstr($requestUrl, '?') ? '&' : '?';

            $pagedRequest = $request->withUri(new Uri($requestUrl . $paramSeparator . http_build_query($params)));

            $response = $this->process($pagedRequest);

            $root = $response->get($rootElement);

            if ($pageResults = empty($root) ? false : $root) {
                $allResults = array_merge($allResults, $pageResults);
            }

            $params['page']++;
        } while ($pageResults);

        return $allResults;
    }
}

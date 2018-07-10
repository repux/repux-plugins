<?php

namespace App\Shopify\Api\Endpoint;

use App\Shopify\Api\GenericResource;
use App\Shopify\Api\Request\GetJson;

class Order extends AbstractEndpoint
{
    /**
     * @param array $query
     *
     * @return array|GenericResource[]
     * @throws \App\Shopify\Api\Request\Exception\FailedRequestException
     */
    public function findAll(array $query = [])
    {
        $request = new GetJson('/admin/orders.json', $query);
        $response = $this->sendPaged($request, 'orders');

        return $this->createCollection($response);
    }

    public function findOne(int $orderId, array $fields = []): GenericResource
    {
        $params = $fields ? ['fields' => implode(',', $fields)] : [];
        $request = new GetJson(sprintf('/admin/orders/%d.json', $orderId), $params);
        $response = $this->send($request);

        return $this->createEntity($response->get('order'));
    }

    public function countAll(array $query = []): int
    {
        $request = new GetJson('/admin/orders/count.json', $query);
        $response = $this->send($request);

        return $response->get('count');
    }
}

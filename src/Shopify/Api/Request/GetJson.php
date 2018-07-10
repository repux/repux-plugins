<?php

namespace App\Shopify\Api\Request;

use GuzzleHttp\Psr7\Request;

class GetJson extends Request
{
    public function __construct(string $url, array $params = [])
    {
        if (!empty($params)) {
            $url .= '?'.http_build_query($params);
        }

        parent::__construct('GET', $url, ['Content-type' => 'application/json']);
    }
}

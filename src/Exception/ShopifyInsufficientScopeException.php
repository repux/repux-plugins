<?php

namespace App\Exception;

class ShopifyInsufficientScopeException extends \RuntimeException
{
    public function __construct(string $requestedScope, string $grantedScope)
    {
        parent::__construct(
            sprintf(
                'Insufficient scope. Requested: "%s", granted: "%s".',
                $requestedScope,
                $grantedScope
            )
        );
    }
}

<?php

namespace App\Security;

use Psr\Http\Message\RequestInterface;

class WebpushMiddleware
{
    public static function auth(string $user, string $password): callable
    {
        return function (callable $handler) use ($user, $password) {
            return function (RequestInterface $request, array $options) use ($handler, $user, $password) {
                $request = $request->withHeader(
                    'Authorization',
                    sprintf('Basic %s', base64_encode(sprintf('%s:%s', $user, $password)))
                );

                return $handler($request, $options);
            };
        };
    }
}

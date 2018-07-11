<?php

namespace Tests\Unit\App\Shopify\Authentication;

use App\Entity\ShopifyStore;
use App\Shopify\Authentication\OAuth;
use App\Security\HmacSignature;
use Codeception\TestCase\Test;
use Codeception\Util\Stub;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Traits\StubEntityManagerTrait;

class OAuthTest extends Test
{
    use StubEntityManagerTrait;

    public function testAuth()
    {
        $shopifyStore = new ShopifyStore();
        $shopifyStore->setName('repux');

        $router = $this->stubRouter([
            'generate' => Stub::once(function () {
                return 'verify_url';
            }),
        ]);

        $config = [
            'api_key' => 'public-api-key',
            'shared_secret' => 'newly-generated-shared-secret',
            'scope' => 'read_orders',
        ];

        $client = $this->stubClient();

        $hmacSignature = new HmacSignature($config['shared_secret']);

        $auth = new OAuth($router, $config, $client, $hmacSignature);

        $result = $auth->auth($shopifyStore);
        $this->assertContains(
            sprintf(
                OAuth::SHOPIFY_AUTH_PATH_PATTERN,
                $shopifyStore->getName(),
                ''
            ),
            $result
        );
        $this->assertNotEmpty($shopifyStore->getNonce());
    }

    public function testVerifyValid()
    {
        $shopifyStore = new ShopifyStore();
        $shopifyStore->setName('repux');
        $shopifyStore->setNonce('5aab6e53d8cde');

        $router = $this->stubRouter();

        $config = [
            'api_key' => 'public-api-key',
            'shared_secret' => 'newly-generated-shared-secret',
            'scope' => 'read_orders',
        ];

        $request = $this->stubRequest(
            [],
            [
                'code' => 'auth-code',
                'shop' => sprintf('%s.myshopify.com', $shopifyStore->getName()),
                'state' => $shopifyStore->getNonce(),
                'hmac' => '8e47c4493bd78904029764a92c2ca23b2a8716844791f2f8bf66c8bfdda0e2b6',
            ]
        );
        $response = $this->stubResponse([
            'getBody' => Stub::once(function () use ($config) {
                return sprintf(
                    '{"access_token": "f85632530bf277ec9ac6f649fc327f17","scope": "%s"}',
                    $config['scope']
                );
            }),
        ]);
        $client = $this->stubClient([
            'request' => Stub::once(function () use ($response) {
                return $response;
            }),
        ]);

        $hmacSignature = new HmacSignature($config['shared_secret']);

        $auth = new OAuth($router, $config, $client, $hmacSignature);

        $auth->verify($shopifyStore, $request);

        $this->assertEmpty($shopifyStore->getNonce());
        $this->assertNotEmpty($shopifyStore->getAccessToken());
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @expectedExceptionMessage Invalid HMAC Signature
     */
    public function testVerifyInvalidHmac()
    {
        $shopifyStore = new ShopifyStore();
        $shopifyStore->setName('repux');
        $shopifyStore->setNonce('5aab6e53d8cde');

        $router = $this->stubRouter();

        $config = [
            'api_key' => 'public-api-key',
            'shared_secret' => 'newly-generated-shared-secret',
            'scope' => 'read_orders',
        ];

        $request = $this->stubRequest(
            [],
            [
                'code' => 'auth-code',
                'shop' => sprintf('%s.myshopify.com', $shopifyStore->getName()),
                'state' => $shopifyStore->getNonce(),
                'hmac' => '8e47c4493bd78904029764a92c2ca23b2a8716844791f2f8bf66c8bfdda0e2b7',
            ]
        );
        $client = $this->stubClient();

        $hmacSignature = new HmacSignature($config['shared_secret']);

        $auth = new OAuth($router, $config, $client, $hmacSignature);

        $auth->verify($shopifyStore, $request);
    }

    /**
     * @expectedException \App\Exception\ShopifyInsufficientScopeException
     * @expectedExceptionMessage Insufficient scope. Requested: "read_orders", granted: "invalid_scope".
     */
    public function testVerifyInvalidScope()
    {
        $shopifyStore = new ShopifyStore();
        $shopifyStore->setName('repux');
        $shopifyStore->setNonce('5aab6e53d8cde');

        $router = $this->stubRouter();

        $config = [
            'api_key' => 'public-api-key',
            'shared_secret' => 'newly-generated-shared-secret',
            'scope' => 'read_orders',
        ];

        $request = $this->stubRequest(
            [],
            [
                'code' => 'auth-code',
                'shop' => sprintf('%s.myshopify.com', $shopifyStore->getName()),
                'state' => $shopifyStore->getNonce(),
                'hmac' => '8e47c4493bd78904029764a92c2ca23b2a8716844791f2f8bf66c8bfdda0e2b6',
            ]
        );
        $response = $this->stubResponse([
            'getBody' => Stub::once(function () {
                return sprintf(
                    '{"access_token": "f85632530bf277ec9ac6f649fc327f17","scope": "%s"}',
                    'invalid_scope'
                );
            }),
        ]);
        $client = $this->stubClient([
            'request' => Stub::once(function () use ($response) {
                return $response;
            }),
        ]);

        $hmacSignature = new HmacSignature($config['shared_secret']);

        $auth = new OAuth($router, $config, $client, $hmacSignature);

        $auth->verify($shopifyStore, $request);
    }

    protected function stubRouter(array $properties = []): UrlGeneratorInterface
    {
        /** @var Router $stub */
        $stub = Stub::makeEmpty(Router::class, $properties);

        return $stub;
    }

    protected function stubClient(array $properties = []): ClientInterface
    {
        /** @var Client $stub */
        $stub = Stub::makeEmpty(Client::class, $properties);

        return $stub;
    }

    protected function stubRequest(array $properties = [], array $parameters = []): Request
    {
        /** @var Request $stub */
        $stub = Stub::construct(Request::class, ['query' => $parameters], $properties);

        return $stub;
    }

    protected function stubResponse(array $properties = []): ResponseInterface
    {
        /** @var Response $stub */
        $stub = Stub::makeEmpty(Response::class, $properties);

        return $stub;
    }
}

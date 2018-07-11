<?php

namespace App\Shopify\Authentication;

use App\Entity\ShopifyStore;
use App\Exception\ShopifyInsufficientScopeException;
use App\Security\HmacSignature;
use GuzzleHttp\ClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @see https://help.shopify.com/api/getting-started/authentication/oauth
 */
class OAuth
{
    const SHOPIFY_AUTH_PATH_PATTERN = 'https://%s.myshopify.com/admin/oauth/authorize?%s';
    const SHOPIFY_ACCESS_TOKEN_PATH_PATTERN = 'https://%s/admin/oauth/access_token';

    private $router;

    /**
     * @var array
     */
    private $config;

    private $client;

    private $hmacSignature;

    public function __construct(
        UrlGeneratorInterface $router,
        array $config,
        ClientInterface $client,
        HmacSignature $hmacSignature
    )
    {
        $this->router = $router;
        $this->config = $config;
        $this->client = $client;
        $this->hmacSignature = $hmacSignature;
    }

    public function auth(ShopifyStore $shopifyStore): string
    {
        $verifyUrl = $this->router->generate('api_shopify_store_verify', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $nonce = uniqid();

        $shopifyStore->setNonce($nonce);

        $params = [
            'client_id' => $this->config['api_key'],
            'scope' => $this->config['scope'],
            'redirect_uri' => $verifyUrl,
            'state' => $nonce,
        ];

        $authorizeUrl = sprintf(self::SHOPIFY_AUTH_PATH_PATTERN, $shopifyStore->getName(), http_build_query($params));

        return $authorizeUrl;
    }

    public function verify(ShopifyStore $shopifyStore, Request $request): ShopifyStore
    {
        $authCode = $request->get('code');
        $storeName = $request->get('shop');
        $hmac = $request->get('hmac');

        if (!$this->hmacSignature->isValid($hmac, $request->query->all())) {
            throw new BadRequestHttpException('Invalid HMAC Signature');
        }

        $params = [
            'body' => \GuzzleHttp\json_encode([
                'client_id' => $this->config['api_key'],
                'client_secret' => $this->config['shared_secret'],
                'code' => $authCode
            ]),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ];

        $response = $this->client->request(
            'POST',
            sprintf(self::SHOPIFY_ACCESS_TOKEN_PATH_PATTERN, $storeName),
            $params
        );
        $responseJson = \GuzzleHttp\json_decode($response->getBody(), true);

        if ($responseJson['scope'] !== $this->config['scope']) {
            throw new ShopifyInsufficientScopeException($this->config['scope'], $responseJson['scope']);
        }

        $accessToken = $responseJson['access_token'];

        $shopifyStore->setNonce(null);
        $shopifyStore->setAccessToken($accessToken);

        return $shopifyStore;
    }
}

<?php

namespace Tests\Functional\Controller;

use App\DataFixtures\test\UserFixture;
use App\Entity\ShopifyStore;
use App\Entity\User;
use App\Shopify\Authentication\OAuth;
use Symfony\Component\HttpFoundation\Response;

class ChannelAmazonControllerCest
{
    const BASE_PATH = '/api/amazon';

    public function _before(\FunctionalTester $I)
    {
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->amTokenAuthenticated(UserFixture::FIRST_USER_ADDRESS);
    }

    public function getOne(\FunctionalTester $I)
    {
        /** @var User $user */
        $user = $I->grabEntityFromRepository(User::class, ['ethAddress' => UserFixture::FIRST_USER_ADDRESS]);
        $channel = $this->haveShopifyStoreInRepository($I, $user, 'my-channel');

        $I->sendGET(sprintf('%s/%s', self::BASE_PATH, $channel->getId()));

        $I->seeResponseCodeIs(Response::HTTP_OK);
    }

    public function getOneFromOtherUser(\FunctionalTester $I)
    {
        /** @var User $user */
        $user = $I->grabEntityFromRepository(User::class, ['ethAddress' => UserFixture::SECOND_USER_ADDRESS]);
        $channel = $this->haveShopifyStoreInRepository($I, $user, 'my-channel');

        $I->sendGET(sprintf('%s/%s', self::BASE_PATH, $channel->getId()));

        $I->seeResponseCodeIs(Response::HTTP_NOT_FOUND);
    }

    public function getNonExistingOne(\FunctionalTester $I)
    {
        $I->sendGET(sprintf('%s/non-existing-id', self::BASE_PATH));

        $I->seeResponseCodeIs(Response::HTTP_NOT_FOUND);
    }

    public function postValidTwiceAndGetOne(\FunctionalTester $I)
    {
        $data = [
            'channel_amazon' => [
                'name' => 'repux',
            ],
        ];

        $I->sendPOST(self::BASE_PATH, $data);

        $I->seeResponseCodeIs(Response::HTTP_CREATED);
        $I->seeResponseMatchesJsonType(
            [
                'channel_amazon' => [
                    'id' => 'integer',
                    'name' => 'string',
                ],
                'authorizeUrl' => 'string',
            ]
        );
        $I->assertContains(
            sprintf(
                OAuth::SHOPIFY_AUTH_PATH_PATTERN,
                $data['channel_amazon']['name'],
                ''
            ),
            json_decode($I->grabResponse(), true)['authorizeUrl']
        );

        $responseData = json_decode($I->grabResponse(), true)['channel_amazon'];

        $nonce = $I->grabFromRepository(
            ShopifyStore::class,
            'nonce',
            [
                'id' => $responseData['id'],
                'name' => $data['channel_amazon']['name'],
            ]
        );
        $I->assertNotEmpty($nonce);

        $I->sendGET(sprintf('%s/%s', self::BASE_PATH, $responseData['id']));

        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseContainsJson([
            'channel_amazon' => [
                'id' => $responseData['id'],
                'name' => $data['channel_amazon']['name'],
                'is_verified' => false,
            ],
        ]);

        $data['channel_amazon']['name'] = 'MOD_repux';

        $I->sendPOST(self::BASE_PATH, $data);

        $I->seeResponseCodeIs(Response::HTTP_CREATED);

        $nonceMod = $I->grabFromRepository(
            ShopifyStore::class,
            'nonce',
            [
                'id' => $responseData['id'],
                'name' => $data['channel_amazon']['name'],
            ]
        );
        $I->assertNotEquals($nonce, $nonceMod);
    }

    public function postEmpty(\FunctionalTester $I)
    {
        $data = [
            'channel_amazon' => [],
        ];
        $I->sendPOST(self::BASE_PATH, $data);

        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseContainsJson([
            'errors' => [
                'children' => [
                    'name' => [
                        'errors' => ['This value should not be blank.'],
                    ],
                ],
            ],
        ]);
    }

    public function postAlreadyIntegrated(\FunctionalTester $I)
    {
        $data = [
            'channel_amazon' => [
                'name' => 'repux',
            ],
        ];

        /** @var User $user */
        $user = $I->grabEntityFromRepository(User::class, ['ethAddress' => UserFixture::FIRST_USER_ADDRESS]);
        $this->haveShopifyStoreInRepository($I, $user, $data['channel_amazon']['name'], 'token');

        $I->sendPOST(self::BASE_PATH, $data);

        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseMatchesJsonType([
            'error' => [],
        ]);
    }

    private function haveShopifyStoreInRepository(
        \FunctionalTester $I,
        User $user,
        string $name,
        string $token = null
    ): ShopifyStore {
        $id = $I->haveInRepository(ShopifyStore::class, [
            'user' => $user,
            'name' => $name,
            'accessToken' => $token,
        ]);

        return $I->grabEntityFromRepository(ShopifyStore::class, ['id' => $id]);
    }
}

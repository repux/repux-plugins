<?php

namespace Tests\Functional\Controller;

use App\DataFixtures\test\UserFixture;
use App\Entity\ShopifyStore;
use App\Entity\ShopifyStoreProcess;
use App\Entity\User;
use Codeception\Example;
use Symfony\Component\HttpFoundation\Response;

class ShopifyStoreProcessControllerCest
{
    const BASE_PATH = '/api/shopify-store-process';

    public function _before(\FunctionalTester $I)
    {
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->amTokenAuthenticated(UserFixture::FIRST_USER_ADDRESS);
    }

    public function getByStoreNotAuthenticated(\FunctionalTester $I)
    {
        $I->amNotTokenAuthenticated();

        $I->sendGET(sprintf('%s/by-store/%s', self::BASE_PATH, 1));

        $I->seeResponseCodeIs(Response::HTTP_UNAUTHORIZED);
    }

    public function getByStore(\FunctionalTester $I)
    {
        /** @var User $user */
        $user = $I->grabEntityFromRepository(User::class, ['ethAddress' => UserFixture::FIRST_USER_ADDRESS]);
        $store = $this->haveShopifyStoreInRepository($I, $user, 'my-store');
        $this->haveShopifyStoreProcessInRepository($I, $store);

        /** @var User $user2 */
        $user2 = $I->grabEntityFromRepository(User::class, ['ethAddress' => UserFixture::SECOND_USER_ADDRESS]);
        $store2 = $this->haveShopifyStoreInRepository($I, $user2, 'my-store-2');
        $this->haveShopifyStoreProcessInRepository($I, $store2);

        $I->sendGET(sprintf('%s/by-store/%s', self::BASE_PATH, $store->getId()));

        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->canSeeResponseContainsJson([
            'meta' => ['total' => 1],
            'shopify_store_processes' => [[]],
        ]);
    }

    public function postShopifyStoreProcess(\FunctionalTester $I)
    {
        /** @var User $user */
        $user = $I->grabEntityFromRepository(User::class, ['ethAddress' => UserFixture::FIRST_USER_ADDRESS]);
        $store = $this->haveShopifyStoreInRepository($I, $user, 'my-store');

        $data = [
            'shopify_store_process' => [
                'shopifyStore' => $store->getId(),
                'parameters' => '{"created_at_from": "2016-01-01 00:00:00", "created_at_from": "2018-01-01 00:01:11"}',
            ],
        ];

        $I->sendPOST(self::BASE_PATH, $data);

        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->canSeeResponseContainsJson([
            'shopify_store_process' => [
                'parameters' => $data['shopify_store_process']['parameters'],
            ],
        ]);
        $I->seeInRepository(ShopifyStoreProcess::class, ['parameters' => $data['shopify_store_process']['parameters']]);
    }

    /**
     * @example(params="{\"created_at_from\": \"not a date\", \"created_at_from\": \"2017-01-01 00:01:11\"}")
     * @example(params="{\"invalid_param\": \"2015-01-01 00:00:00\", \"created_at_from\": \"2017-01-01 00:01:11\"}")
     * @example(params="{\"created_at_from\": \"not a date\"}")
     */
    public function postShopifyStoreProcessWithInvalidParameters(\FunctionalTester $I, Example $example)
    {
        /** @var User $user */
        $user = $I->grabEntityFromRepository(User::class, ['ethAddress' => UserFixture::FIRST_USER_ADDRESS]);
        $store = $this->haveShopifyStoreInRepository($I, $user, 'my-store');

        $data = [
            'shopify_store_process' => [
                'shopifyStore' => $store->getId(),
                'parameters' => $example['params'],
            ],
        ];

        $I->sendPOST(self::BASE_PATH, $data);

        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->dontSeeInRepository(
            ShopifyStoreProcess::class,
            ['parameters' => $data['shopify_store_process']['parameters']]
        );
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

    private function haveShopifyStoreProcessInRepository(\FunctionalTester $I, ShopifyStore $store): ShopifyStoreProcess
    {
        $id = $I->haveInRepository(ShopifyStoreProcess::class, [
            'shopifyStore' => $store,
            'status' => ShopifyStoreProcess::STATUS_IDLE,
        ]);

        return $I->grabEntityFromRepository(ShopifyStoreProcess::class, ['id' => $id]);
    }
}

<?php

namespace Tests\Functional\Controller;

use App\DataFixtures\test\UserFixture;
use App\Entity\AmazonChannel;
use App\Entity\AmazonChannelProcess;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class AmazonChannelProcessControllerCest
{
    const BASE_PATH = '/api/amazon-process';

    public function _before(\FunctionalTester $I)
    {
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->amTokenAuthenticated(UserFixture::FIRST_USER_ADDRESS);
    }

    public function getByChannelNotAuthenticated(\FunctionalTester $I)
    {
        $I->amNotTokenAuthenticated();

        $I->sendGET(sprintf('%s/by-channel/%s', self::BASE_PATH, 1));

        $I->seeResponseCodeIs(Response::HTTP_UNAUTHORIZED);
    }

    public function getByChannel(\FunctionalTester $I)
    {
        /** @var User $user */
        $user = $I->grabEntityFromRepository(User::class, ['ethAddress' => UserFixture::FIRST_USER_ADDRESS]);
        $channel = $this->haveAmazonChannelInRepository($I, $user, 'my-channel');
        $this->haveAmazonChannelProcessInRepository($I, $channel);

        /** @var User $user2 */
        $user2 = $I->grabEntityFromRepository(User::class, ['ethAddress' => UserFixture::SECOND_USER_ADDRESS]);
        $channel2 = $this->haveAmazonChannelInRepository($I, $user2, 'my-channel-2');
        $this->haveAmazonChannelProcessInRepository($I, $channel2);

        $I->sendGET(sprintf('%s/by-channel/%s', self::BASE_PATH, $channel->getId()));

        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->canSeeResponseContainsJson([
            'meta' => ['total' => 1],
            'amazon_channel_processes' => [[]],
        ]);
    }

    public function postAmazonChannelProcess(\FunctionalTester $I)
    {
        /** @var User $user */
        $user = $I->grabEntityFromRepository(User::class, ['ethAddress' => UserFixture::FIRST_USER_ADDRESS]);
        $channel = $this->haveAmazonChannelInRepository($I, $user, 'my-channel');

        $data = [
            'amazon_channel_process' => [
                'amazonChannel' => $channel->getId(),
                'parameters' => 'some-param',
                'type' => AmazonChannelProcess::TYPE_IMPORT_ORDERS,
            ],
        ];

        $I->sendPOST(self::BASE_PATH, $data);

        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->canSeeResponseContainsJson([
            'amazon_channel_process' => [
                'parameters' => $data['amazon_channel_process']['parameters'],
            ],
        ]);
        $I->seeInRepository(AmazonChannelProcess::class, ['parameters' => $data['amazon_channel_process']['parameters']]);
    }

    private function haveAmazonChannelInRepository(
        \FunctionalTester $I,
        User $user,
        string $name
    ): AmazonChannel {
        $id = $I->haveInRepository(AmazonChannel::class, [
            'user' => $user,
            'name' => $name,
            'merchantId' => 'merchant-id',
            'marketplaceId' => 'marketplace-id',
            'apiToken' => 'api-token',
        ]);

        return $I->grabEntityFromRepository(AmazonChannel::class, ['id' => $id]);
    }

    private function haveAmazonChannelProcessInRepository(\FunctionalTester $I, AmazonChannel $channel): AmazonChannelProcess
    {
        $id = $I->haveInRepository(AmazonChannelProcess::class, [
            'amazonChannel' => $channel,
            'status' => AmazonChannelProcess::STATUS_IDLE,
            'type' => AmazonChannelProcess::TYPE_IMPORT_ORDERS,
        ]);

        return $I->grabEntityFromRepository(AmazonChannelProcess::class, ['id' => $id]);
    }
}

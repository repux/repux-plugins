<?php

namespace Tests\Functional\Controller;

use App\DataFixtures\test\UserFixture;
use App\Entity\ChannelAmazon;
use App\Entity\ChannelAmazonProcess;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class ChannelAmazonProcessControllerCest
{
    const BASE_PATH = '/api/amazon-process';

    public function _before(\FunctionalTester $I)
    {
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->amTokenAuthenticated(UserFixture::FIRST_USER_ADDRESS);
    }

    public function getByStoreNotAuthenticated(\FunctionalTester $I)
    {
        $I->amNotTokenAuthenticated();

        $I->sendGET(sprintf('%s/by-channel/%s', self::BASE_PATH, 1));

        $I->seeResponseCodeIs(Response::HTTP_UNAUTHORIZED);
    }

    public function getByStore(\FunctionalTester $I)
    {
        /** @var User $user */
        $user = $I->grabEntityFromRepository(User::class, ['ethAddress' => UserFixture::FIRST_USER_ADDRESS]);
        $channel = $this->haveChannelAmazonInRepository($I, $user, 'my-channel');
        $this->haveChannelAmazonProcessInRepository($I, $channel);

        /** @var User $user2 */
        $user2 = $I->grabEntityFromRepository(User::class, ['ethAddress' => UserFixture::SECOND_USER_ADDRESS]);
        $channel2 = $this->haveChannelAmazonInRepository($I, $user2, 'my-channel-2');
        $this->haveChannelAmazonProcessInRepository($I, $channel2);

        $I->sendGET(sprintf('%s/by-channel/%s', self::BASE_PATH, $channel->getId()));

        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->canSeeResponseContainsJson([
            'meta' => ['total' => 1],
            'channel_amazon_processes' => [[]],
        ]);
    }

    public function postChannelAmazonProcess(\FunctionalTester $I)
    {
        /** @var User $user */
        $user = $I->grabEntityFromRepository(User::class, ['ethAddress' => UserFixture::FIRST_USER_ADDRESS]);
        $channel = $this->haveChannelAmazonInRepository($I, $user, 'my-channel');

        $data = [
            'channel_amazon_process' => [
                'channelAmazon' => $channel->getId(),
                'parameters' => 'some-param'
            ],
        ];

        $I->sendPOST(self::BASE_PATH, $data);

        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->canSeeResponseContainsJson([
            'channel_amazon_process' => [
                'parameters' => $data['channel_amazon_process']['parameters'],
            ],
        ]);
        $I->seeInRepository(ChannelAmazonProcess::class, ['parameters' => $data['channel_amazon_process']['parameters']]);
    }

    private function haveChannelAmazonInRepository(
        \FunctionalTester $I,
        User $user,
        string $name,
        string $token = null
    ): ChannelAmazon {
        $id = $I->haveInRepository(ChannelAmazon::class, [
            'user' => $user,
            'name' => $name,
            'accessToken' => $token,
        ]);

        return $I->grabEntityFromRepository(ChannelAmazon::class, ['id' => $id]);
    }

    private function haveChannelAmazonProcessInRepository(\FunctionalTester $I, ChannelAmazon $channel): ChannelAmazonProcess
    {
        $id = $I->haveInRepository(ChannelAmazonProcess::class, [
            'channelAmazon' => $channel,
            'status' => ChannelAmazonProcess::STATUS_IDLE,
        ]);

        return $I->grabEntityFromRepository(ChannelAmazonProcess::class, ['id' => $id]);
    }
}

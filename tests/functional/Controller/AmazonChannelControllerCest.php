<?php

namespace Tests\Functional\Controller;

use App\DataFixtures\test\UserFixture;
use App\Entity\AmazonChannel;
use App\Entity\User;
use donatj\MockWebServer\MockWebServer;
use Symfony\Component\HttpFoundation\Response;

class AmazonChannelControllerCest
{
    const BASE_PATH = '/api/amazon';

    /** @var MockWebServer */
    private $server;

    public function _before(\FunctionalTester $I)
    {
        $I->haveHttpHeader('Content-type', 'application/json');
        $I->amTokenAuthenticated(UserFixture::FIRST_USER_ADDRESS);

        if (!$this->server) {
            $this->server = new MockWebServer(18080, '0.0.0.0');
            $this->server->start();
        }
    }

    public function _afterSuite(\FunctionalTester $I)
    {
        $this->server->stop();
        $this->server = null;
    }

    public function getList(\FunctionalTester $I)
    {
        /** @var User $user */
        $user = $I->grabEntityFromRepository(User::class, ['ethAddress' => UserFixture::FIRST_USER_ADDRESS]);
        $this->haveAmazonChannelInRepository($I, $user, 'my-channel');

        /** @var User $user2 */
        $user2 = $I->grabEntityFromRepository(User::class, ['ethAddress' => UserFixture::SECOND_USER_ADDRESS]);
        $this->haveAmazonChannelInRepository($I, $user2, 'my-channel-2');

        $I->sendGET(self::BASE_PATH);

        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->canSeeResponseContainsJson([
            'meta' => ['total' => 1],
            'amazon_channels' => [[]],
        ]);
    }

    public function post(\FunctionalTester $I)
    {
        $data = [
            'amazon_channel' => [
                'name' => 'repux',
                'merchantId' => 'merchant',
                'marketplaceId' => 'A2EUQ1WTGCTBG2',
                'apiToken' => 'token',
            ],
        ];

        $this->amazonMockRequest('GetReportRequestList', 'ok_empty');

        $I->sendPOST(self::BASE_PATH, $data);

        $request = $this->server->getLastRequest();
        $I->assertArraySubset(
            [
                'Merchant' => 'merchant',
                'MWSAuthToken' => 'token',
                'AWSAccessKeyId' => 'AKIAJ2HEQBAI67VXRRZQ',
            ],
            $request->getParsedInput()
        );

        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseIsJson();
        $response = json_decode($I->grabResponse(), true);
        $I->seeInRepository(
            AmazonChannel::class,
            [
                'id' => $response['amazon_channel']['id'],
                'merchantId' => $response['amazon_channel']['merchant_id'],
                'marketplaceId' => $response['amazon_channel']['marketplace_id'],
            ]
        );
    }

    public function postEmpty(\FunctionalTester $I)
    {
        $data = [
            'amazon_channel' => [],
        ];
        $I->sendPOST(self::BASE_PATH, $data);

        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
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

    private function amazonMockRequest(string $action, string $responseFile, string $path = '/', int $status = 200)
    {
        $this->server->setResponseOfPath(
            $path,
            new \donatj\MockWebServer\Response(
                file_get_contents(codecept_data_dir(sprintf('amazon/response/%s/%s.xml', $action, $responseFile))),
                [
                    'x-mws-request-id' => 1,
                    'x-mws-response-context' => 'context',
                    'x-mws-timestamp' => time(),
                ],
                $status
            )
        );
    }
}

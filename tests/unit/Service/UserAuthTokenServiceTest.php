<?php

namespace Tests\Unit\Service;

use App\Entity\User;
use App\Entity\UserAuthToken;
use App\Service\UserAuthTokenService;
use Traits\StubDateTimeFactoryServiceTrait;
use Traits\StubEntityManagerTrait;
use Codeception\Stub;
use Codeception\TestCase\Test;

class UserAuthTokenServiceTest extends Test
{
    use StubEntityManagerTrait, StubDateTimeFactoryServiceTrait;

    public function testGenerateTokenFor()
    {
        $user = new User();
        $user->setEthAddress('0x123');

        $persist = Stub\Expected::once(function (UserAuthToken $entity) use ($user) {
            $this->assertEquals($user->getEthAddress(), $entity->getUser()->getEthAddress());
        });

        $flush = Stub\Expected::once(function (UserAuthToken $entity) use ($user) {
            $this->assertEquals($user->getEthAddress(), $entity->getUser()->getEthAddress());
        });

        $now = new \DateTime('2018-01-01 01:02:03');
        $dateTimeFactoryService = $this->stubDateTimeFactoryService($now);
        $entityManager = $this->stubEntityManager(
            [UserAuthToken::class => null],
            [
                'persist' => $persist,
                'flush' => $flush,
            ]
        );
        $service = new UserAuthTokenService($dateTimeFactoryService, $entityManager);

        $token = $service->generateTokenFor($user);
        $this->assertInstanceOf(UserAuthToken::class, $token);
        $this->assertNotEmpty($token->getHash());
        $this->assertEquals(
            $token->getExpiresAt()->getTimestamp(),
            $now->modify(UserAuthTokenService::TOKEN_EXPIRY_TIME)->getTimestamp()
        );
    }
}

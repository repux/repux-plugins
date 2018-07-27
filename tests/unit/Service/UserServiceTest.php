<?php

namespace Tests\Unit\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Traits\StubEntityManagerTrait;
use Codeception\Stub;
use Codeception\TestCase\Test;

class UserServiceTest extends Test
{
    use StubEntityManagerTrait;

    public function testGetUserByAddress()
    {
        $address = '0x123';
        $findOneBy = Stub\Expected::once(function ($criteria) use ($address) {
            $this->assertEquals(['ethAddress' => $address], $criteria);
        });
        /** @var UserRepository $userRepository */
        $userRepository = Stub::make(UserRepository::class, ['findOneBy' => $findOneBy], $this);
        $entityManager = $this->stubEntityManager([User::class => $userRepository]);
        $service = new UserService($entityManager);

        $service->getUserByAddress($address);
    }

    public function testCreateUserByAddress()
    {
        $address = '0x123';
        $entityManager = $this->stubEntityManager([User::class => null]);
        $service = new UserService($entityManager);

        $user = $service->createUserByAddress($address);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($address, $user->getEthAddress());
    }

    public function testSaveUser()
    {
        $user = new User();
        $user->setEthAddress('0x123');

        $persist = Stub\Expected::once(function (User $entity) use ($user) {
            $this->assertEquals($user->getEthAddress(), $entity->getEthAddress());
        });

        $flush = Stub\Expected::once(function (User $entity) use ($user) {
            $this->assertEquals($user->getEthAddress(), $entity->getEthAddress());
        });

        $entityManager = $this->stubEntityManager([User::class => null], ['persist' => $persist, 'flush' => $flush]);
        $service = new UserService($entityManager);

        $service->saveUser($user);
    }
}

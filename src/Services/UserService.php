<?php

namespace App\Services;

use App\Entity\User;
use Doctrine\ORM\EntityManager;

class UserService
{
    private $entityManager;

    private $userRepository;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $entityManager->getRepository(User::class);
    }

    public function getUserByAddress(string $address): ?User
    {
        return $this->userRepository->findOneBy(['ethAddress' => $address]);
    }

    public function createUserByAddress(string $address): User
    {
        $user = new User();
        $user->setEthAddress($address);

        return $user;
    }

    public function saveUser(User $user)
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush($user);
    }
}

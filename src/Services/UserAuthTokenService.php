<?php

namespace App\Services;

use App\Entity\User;
use App\Entity\UserAuthToken;
use Doctrine\ORM\EntityManager;

class UserAuthTokenService
{
    const TOKEN_EXPIRY_TIME = '+1 day';

    private $entityManager;

    private $dateTimeFactoryService;

    private $userAuthTokenRepository;

    public function __construct(DateTimeFactoryService $dateTimeFactoryService, EntityManager $entityManager)
    {
        $this->dateTimeFactoryService = $dateTimeFactoryService;
        $this->entityManager = $entityManager;
        $this->userAuthTokenRepository = $entityManager->getRepository(UserAuthToken::class);
    }

    public function generateTokenFor(User $user): UserAuthToken
    {
        $hash = $this->generateTokenHash();
        $now = \DateTimeImmutable::createFromFormat(
            \DateTime::ISO8601,
            $this->dateTimeFactoryService->now()->format(\DateTime::ISO8601)
        );

        $token = new UserAuthToken();
        $token->setUser($user);
        $token->setHash($hash);
        $token->setExpiresAt($now->modify(self::TOKEN_EXPIRY_TIME));

        $this->entityManager->persist($token);
        $this->entityManager->flush($token);

        return $token;
    }

    public function invalidateToken(string $hash)
    {
        $this->userAuthTokenRepository->createQueryBuilder('userAuthToken')
            ->delete()
            ->where('userAuthToken.hash = :hash')
            ->setParameter('hash', $hash)
            ->getQuery()
            ->execute();
    }

    private function generateTokenHash(): string
    {
        $bytes = openssl_random_pseudo_bytes(32);

        return hash('sha512', sprintf('%s%s', bin2hex($bytes), microtime()));
    }
}

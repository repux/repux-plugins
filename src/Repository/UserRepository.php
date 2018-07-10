<?php

namespace App\Repository;

use App\Repository\Traits\RepositoryAliasTrait;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository implements UserLoaderInterface
{
    use RepositoryAliasTrait;

    const ALIAS = 'user';

    public function loadUserByUsername($username)
    {
        $qb = $this->createQueryBuilder(self::ALIAS);
        $qb
            ->innerJoin(self::getAliasedFieldName('authTokens'), UserAuthTokenRepository::ALIAS)
            ->where($qb->expr()->andX(
                $qb->expr()->eq(UserAuthTokenRepository::getAliasedFieldName('hash'), ':hash'),
                $qb->expr()->gt(UserAuthTokenRepository::getAliasedFieldName('expiresAt'), 'CURRENT_TIMESTAMP()')
            ))
            ->setParameter('hash', $username);

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }
}

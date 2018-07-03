<?php

namespace App\Repository;

use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository implements UserLoaderInterface
{
    public function loadUserByUsername($username)
    {
        $qb = $this->createQueryBuilder('u');
        $qb
            ->innerJoin('u.authTokens', 'userAuthToken')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('userAuthToken.hash', ':hash'),
                $qb->expr()->gt('userAuthToken.expiresAt', 'CURRENT_TIMESTAMP()')
            ))
            ->setParameter('hash', $username);

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }
}

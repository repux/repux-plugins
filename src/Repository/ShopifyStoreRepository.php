<?php

namespace App\Repository;

use App\Entity\User;
use App\Repository\Traits\RepositoryAliasTrait;
use App\Repository\Traits\UserAwareQueryBuilderTrait;
use Doctrine\ORM\EntityRepository;

class ShopifyStoreRepository extends EntityRepository
{
    use RepositoryAliasTrait, UserAwareQueryBuilderTrait;

    const ALIAS = 'shopifyStore';

    public function findOne(User $user, string $id)
    {
        $qb = $this->createQueryBuilderForUser($user);

        $qb
            ->andWhere($qb->expr()->eq(self::getAliasedFieldName('id'), ':id'))
            ->setParameter('id', $id);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAllByUser(User $user)
    {
        $qb = $this->createQueryBuilderForUser($user);

        return $qb->getQuery()->getResult();
    }
}

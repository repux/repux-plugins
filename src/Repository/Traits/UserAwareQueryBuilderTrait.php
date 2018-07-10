<?php

namespace App\Repository\Traits;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;

trait UserAwareQueryBuilderTrait
{
    public function createQueryBuilderForUser(User $user): QueryBuilder
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->createQueryBuilder(self::ALIAS);

        $queryBuilder
            ->andWhere($queryBuilder->expr()->eq(self::getAliasedFieldName('user'), ':user'))
            ->setParameter('user', $user->getId());

        return $queryBuilder;
    }
}

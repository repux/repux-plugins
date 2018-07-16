<?php

namespace App\Repository;

use App\Repository\Traits\RepositoryAliasTrait;
use App\Repository\Traits\UserAwareQueryBuilderTrait;
use Doctrine\ORM\EntityRepository;

class AmazonChannelRepository extends EntityRepository
{
    use RepositoryAliasTrait, UserAwareQueryBuilderTrait;

    const ALIAS = 'amazonChannel';

    public function getById(string $id)
    {
        $builder = $this->createQueryBuilder('c');
        $query = $builder
            ->where('c.id = :id')
            ->setParameters([
                'id' => $id,
            ])
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}

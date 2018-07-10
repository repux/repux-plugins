<?php

namespace App\Repository;

use App\Repository\Traits\RepositoryAliasTrait;
use Doctrine\ORM\EntityRepository;

class DataFileRepository extends EntityRepository
{
    use RepositoryAliasTrait;

    const ALIAS = 'dataFile';

    public function findOneByFileHash(string $fileHash)
    {
        return $this->findOneBy([
            'deletedAt' => null,
            'fileHash' => $fileHash,
        ]);
    }

    public function findOneByContractAddressWithDifferentFileHash(string $address, string $fileHash)
    {
        $qb = $qb = $this->createQueryBuilder();
        $qb
            ->andWhere($qb->expr()->eq(self::getAliasedFieldName('dataProductContractAddress'), ':address'))
            ->andWhere($qb->expr()->neq(self::getAliasedFieldName('fileHash'), ':fileHash'))
            ->setParameter('address', $address)
            ->setParameter('fileHash', $fileHash)
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function countByUser(string $userId): int
    {
        $qb = $this->createQueryBuilder();
        $qb
            ->select(sprintf('COUNT(%s)', self::getAliasedFieldName('id')))
            ->andWhere($qb->expr()->eq(self::getAliasedFieldName('user'), ':user'))
            ->andWhere($qb->expr()->eq(self::getAliasedFieldName('published'), ':published'))
            ->setParameter('user', $userId)
            ->setParameter('published', 1)
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }
}

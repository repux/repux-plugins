<?php

namespace App\Handler;

use App\Entity\DataFile;
use App\Entity\User;
use App\Repository\DataFileRepository;
use App\Request\ParamsHandler\DataFileParamsHandler;
use App\Service\CurrentUserService;
use App\Service\FileStorageService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

class DataFileHandler
{
    protected $entityManager;

    protected $repository;

    protected $fileStorage;

    protected $currentUserService;

    public function __construct(
        EntityManager $entityManager,
        FileStorageService $fileStorage,
        CurrentUserService $currentUserService
    ) {
        $this->entityManager = $entityManager;
        $this->fileStorage = $fileStorage;
        $this->currentUserService = $currentUserService;

        $this->repository = $entityManager->getRepository(DataFile::class);
    }

    public function create(DataFile $dataFile, User $user): DataFile
    {
        $dataFile->setUser($user);

        $fileId = $this->fileStorage->upload($dataFile);

        $dataFile->setFileId($fileId);
        $dataFile->setOriginalName($dataFile->getUploadedFile()->getClientOriginalName());
        $dataFile->setFileSize($dataFile->getUploadedFile()->getSize());
        $dataFile->setFileMimeType($dataFile->getUploadedFile()->getClientMimeType());

        $this->entityManager->persist($dataFile);
        $this->entityManager->flush($dataFile);

        return $dataFile;
    }

    public function delete(DataFile $dataFile)
    {
        $this->entityManager->remove($dataFile);
        $this->entityManager->flush($dataFile);

        $this->fileStorage->remove($dataFile);
    }

    public function findAll(DataFileParamsHandler $paramsHandler): array
    {
        $qb = $this->createQueryBuilder();
        $this->applyParamsHandler($qb, $paramsHandler, false);

        return $qb->getQuery()->getResult();
    }

    public function countAll(DataFileParamsHandler $paramsHandler): int
    {
        $qb = $this->createQueryBuilder();
        $qb->select(sprintf('COUNT(%s)', DataFileRepository::ALIAS));

        $this->applyParamsHandler($qb, $paramsHandler, true);

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findOne(string $id)
    {
        $qb = $this->createQueryBuilder();

        $qb
            ->andWhere($qb->expr()->eq(DataFileRepository::getAliasedFieldName('id'), ':id'))
            ->setParameter('id', $id);

        return $qb->getQuery()->getOneOrNullResult();
    }

    protected function createQueryBuilder(): QueryBuilder
    {
        $qb = $this->repository->createQueryBuilder(DataFileRepository::ALIAS);

        $qb
            ->andWhere($qb->expr()->eq(DataFileRepository::getAliasedFieldName('user'), ':currentUser'))
            ->setParameter('currentUser', $this->currentUserService->getUser()->getId());

        return $qb;
    }

    protected function applyParamsHandler(QueryBuilder $qb, DataFileParamsHandler $paramsHandler, bool $count)
    {
        if (!$count) {
            $qb
                ->setMaxResults($paramsHandler->getLimit())
                ->setFirstResult(($paramsHandler->getPage() - 1) * $paramsHandler->getLimit());

            if ($paramsHandler->getSortBy()) {
                $qb->addOrderBy($paramsHandler->getSortBy(), $paramsHandler->getSortDirection());
            }
        }
    }
}

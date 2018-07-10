<?php

namespace App\Service;

use App\Entity\DataFile;
use App\FileStorage\FileStorageInterface;

class FileStorageService
{
    protected $fileStorage;

    public function __construct(FileStorageInterface $fileStorage)
    {
        $this->fileStorage = $fileStorage;
    }

    public function upload(DataFile $dataFile): string
    {
        return $this->fileStorage->upload($dataFile);
    }

    public function remove(DataFile $dataFile)
    {
        return $this->fileStorage->remove($dataFile);
    }

    public function getPath(DataFile $dataFile): string
    {
        return $this->fileStorage->getPath($dataFile);
    }
}

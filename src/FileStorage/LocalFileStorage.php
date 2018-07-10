<?php

namespace App\FileStorage;

use App\Entity\DataFile;

class LocalFileStorage implements FileStorageInterface
{
    protected $storageDir;

    public function __construct(string $storageDir)
    {
        $this->storageDir = rtrim($storageDir, '/\\').'/';
    }

    public function upload(DataFile $dataFile): string
    {
        $fileName = sprintf('%d_%s', time(), uniqid());
        $filePath = $this->storageDir.$fileName;

        copy($dataFile->getUploadedFile()->getPathname(), $filePath);

        return $fileName;
    }

    public function remove(DataFile $dataFile)
    {
        @unlink($dataFile->getFileId());
    }

    public function getPath(DataFile $dataFile): string
    {
        return $this->storageDir . $dataFile->getFileId();
    }
}

<?php

namespace App\FileStorage;

use App\Entity\DataFile;

interface FileStorageInterface
{
    public function upload(DataFile $dataFile): string;

    public function remove(DataFile $dataFile);

    public function getPath(DataFile $dataFile): string;
}

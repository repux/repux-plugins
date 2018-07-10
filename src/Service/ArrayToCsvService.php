<?php

namespace App\Service;

class ArrayToCsvService
{
    public function save(string $filename, array $data)
    {
        if (empty($data)) {
            return;
        }

        $addHeader = false;
        if (!is_file($filename)) {
            $addHeader = true;
        }

        $file = new \SplFileObject($filename, 'a+');

        if ($addHeader) {
            $header = array_keys($data);
            $file->fputcsv($header);
        }

        $file->fputcsv($data);
    }
}

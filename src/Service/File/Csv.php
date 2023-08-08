<?php

namespace App\Service\File;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Csv
{
    public function readFile($path)
    {
        if (!file_exists($path) || !is_readable($path)) {
            throw new Exception('Brak pliku');
        }

        return file_get_contents($path);
    }

    public function moveFile(UploadedFile $fileName): string
    {
        $newFileName = uniqid() . '.' . $fileName->guessExtension();
        $newFilePath = '..'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'users'.DIRECTORY_SEPARATOR.'import'.DIRECTORY_SEPARATOR;

        $fileName->move($newFilePath, $newFileName);

        return $newFilePath.$newFileName;
    }
}
<?php
// src/Service/FileService.php
namespace App\Api\Service;


use Symfony\Component\HttpFoundation\RequestStack;


class FileService
{
    public function __construct(
        private readonly RequestStack $requestStack
    ) {}
    public function uploadFile($file, $directory, $userUuid){

        if ($file) {
            $userFolder = $directory . '/' . $userUuid;
            
            if (!is_dir($userFolder)) {
                mkdir($userFolder, 0755, true);
            }
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->guessExtension();
            $safeFilename = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $originalFilename);

            $newFilename = $safeFilename . '.' . $extension;
            $i = 1;
            while (file_exists($userFolder . '/' . $newFilename)) {
                $newFilename = $safeFilename . '_' . $i . '.' . $extension;
                $i++;
            }
            $file->move(
                    $userFolder,
                    $newFilename);
        }
    }
    
}

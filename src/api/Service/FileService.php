<?php
// src/Service/FileService.php
namespace App\Api\Service;

use App\Storage\Repository\FileRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Api\Service\UserService;


class FileService
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly FileRepository $fileRepository,
        private readonly UserService $userService
    ) {}
    public function findAllUserFiles($user)
    {
        return $this->fileRepository->findAllUserFiles($user);
    }
    public function uploadFile($file, $directory, $userUuid)
    {

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

            $fileData = [
                'originalFilename' => $file->getClientOriginalName(),
                'mimeType' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'storagePath' => 'uploads/' . $userUuid . '/' . $newFilename,
            ];

            $file->move(
                $userFolder,
                $newFilename
            );

            $user = $this->userService->findUserByUuid($userUuid);
            $this->fileRepository->saveFile($fileData, $user);
        }
    }
}

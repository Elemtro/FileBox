<?php
// src/Service/FileService.php
namespace App\Api\Service;

use App\Storage\Repository\FileRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Api\Service\UserService;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

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
    public function downloadFile($storagePath, $directory, $userUuid)
    {
        $file = $this->fileRepository->findOneByUserUuidAndFilePath($userUuid, $storagePath);
        if (!$file) {
            throw new NotFoundHttpException('File not found.');
        }
        $fullPath = $directory . $storagePath;


        if (!file_exists($fullPath)) {
            throw new NotFoundHttpException('The file does not physically exist.');
        }



        $response = new BinaryFileResponse($fullPath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $file->getOriginalFilename()
        );
        return $response;
    }
    public function deleteFile($storagePath, $directory, $userUuid)
    {
        $file = $this->fileRepository->findOneByUserUuidAndFilePath($userUuid, $storagePath);
        if (!$file) {
            throw new NotFoundHttpException('File not found.');
        }
        $fullPath = $directory . $storagePath;

        $filesystem = new Filesystem();
        try {
            if ($filesystem->exists($fullPath)) {
                $filesystem->remove($fullPath);
            }

            $this->fileRepository->deleteFile($file);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function uploadFile($file, $directory, $userUuid)
    {

        if ($file) {
            $userFolder = $directory . $userUuid;
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
                'storagePath' => $userUuid . '/' . $newFilename,
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

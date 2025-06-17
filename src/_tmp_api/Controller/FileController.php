<?php
// src/Controller/FileController.php
namespace App\Api\Controller;

use App\Api\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Api\Service\FileService;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FileController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly FileService $fileService
    ) {}
    #[Route('/file/upload', name: 'upload_page', methods: ['GET'])]
    public function uploadFile(): Response
    {
        if (!($this->authService->getSession()->has('user_uuid'))) {
            return $this->redirectToRoute('login_form');
        }
        return $this->render('file/upload.html.twig');
    }

    #[Route('/api/file/upload', name: 'file_upload', methods: ['POST'])]
    public function upload(Request $request): RedirectResponse
    {
        $userUuid = $this->authService->getSession()->get('user_uuid');
        if (!$userUuid) {
            return $this->redirectToRoute('login_form');
        }
        try {
            $file = $request->files->get('file');
            $directory = $this->getParameter('uploads_directory');

            $this->fileService->uploadFile($file, $directory, $userUuid);
            
            $this->addFlash('success', 'File was loaded!');
        } catch (FileException $e) {
            $this->addFlash('error', 'Error loading file.');
        }


        return new RedirectResponse($this->generateUrl('upload_page'));
    }
}

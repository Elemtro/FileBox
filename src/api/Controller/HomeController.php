<?php
// src/Controller/FileController.php
namespace App\Api\Controller;

use App\Api\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Api\Service\FileService;
use App\Api\Service\UserService;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly FileService $fileService,
        private readonly UserService $userService
    ) {}
    #[Route('/home', name: 'api_home', methods: ['GET'])]
    public function uploadFile(): Response
    {
        if (!($this->authService->getSession()->has('user_uuid'))) {
            return $this->redirectToRoute('login_form');
        }
        $uuid = $this->authService->getSession()->get('user_uuid');
        $user = $this->userService->findUserByUuid($uuid);
        $files = $this->fileService->findAllUserFiles($user);
        return $this->render('home/index.html.twig', [
            'files' => $files,
        ]);
    }

}

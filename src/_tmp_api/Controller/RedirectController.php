<?php
namespace App\Api\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RedirectController extends AbstractController
{
    #[Route('/', name: 'root_redirect', methods: ['GET'])]
    public function redirectToHome(): RedirectResponse
    {
        return $this->redirectToRoute('api_home');
    }
}

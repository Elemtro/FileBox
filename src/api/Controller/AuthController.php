<?php

namespace App\Api\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Api\Dto\RegistrationRequest;
use App\Api\Dto\LoginRequest;
use App\Api\Service\AuthService;

class AuthController extends AbstractController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
        private readonly AuthService $authService,
        private readonly TokenStorageInterface $tokenStorage
    ) {}
    #[Route('/login', name: 'login_form', methods: ['GET'])]
    public function showLoginForm(): Response
    {
        return $this->render('auth/login.html.twig');
    }

   #[Route('/register', name: 'register_form', methods: ['GET'])]
    public function showRegisterForm(): Response
    {
        return $this->render('auth/register.html.twig');
    }

    #[Route('/api/auth/login', name: 'api_login', methods: ['POST'])]
    public function login(LoginRequest $dto): JsonResponse
    {
        if ($this->authService->getSession()->has('user_uuid')) {
            return $this->json([
                'message' => 'You are already logged in.'
            ], Response::HTTP_BAD_REQUEST);
        }
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return $this->json([
                'message' => 'Validation failed',
                'errors' => array_map(
                    fn($error) => $error->getPropertyPath() . ': ' . $error->getMessage(),
                    iterator_to_array($errors)
                )
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->authService->login($dto);

            return $this->json([
                'message' => 'Login successful!',
                'user_uuid' => $user->getUuid()->toRfc4122(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Authentication failed',
                'error' => $e->getMessage(),
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

    #[Route('/api/auth/register', name: 'api_register', methods: ['POST'])]
    public function register(RegistrationRequest $dto): JsonResponse
    {
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }

            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $errorMessages
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $this->authService->register($dto);

            return new JsonResponse(['message' => 'User registered successfully!'], JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error while registering user',
                'error' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/auth/logout', name: 'api_logout', methods: ['GET'])]
    public function logout(): RedirectResponse
    {
        $this->tokenStorage->setToken(null);
        $this->authService->logout();

        return new RedirectResponse($this->generateUrl('login_form'));
    }
}

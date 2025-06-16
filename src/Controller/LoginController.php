<?php
// src/Controller/SecurityController.php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response; // Import Response for HTTP status codes
use Symfony\Component\HttpFoundation\Session\SessionInterface; // For session management
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface; // Needed for logging out
use Symfony\Component\Uid\Uuid; // Still needed for Uuid::fromString if parsing from session

class LoginController extends AbstractController
{
    /**
     * Handles user login with manual email/password verification and session management.
     *
     * @param Request $request The incoming HTTP request, expecting JSON payload.
     * @param UserRepository $userRepository Doctrine repository for User entities.
     * @param UserPasswordHasherInterface $passwordHasher Service to verify user passwords.
     * @param SessionInterface $session Symfony's session service for managing sessions.
     * @param TokenStorageInterface $tokenStorage Symfony's token storage service for security context.
     * @return JsonResponse A JSON response indicating login status.
     */
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        SessionInterface $session,
        TokenStorageInterface $tokenStorage // Inject TokenStorageInterface
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Basic input validation
        if (empty($data['email']) || empty($data['password'])) {
            return new JsonResponse(['message' => 'Email and password are required.'], Response::HTTP_BAD_REQUEST);
        }

        $email = $data['email'];
        $password = $data['password'];

        $user = $userRepository->findOneByEmail($email);

        // 1. Check if user exists
        if (!$user) {
            return new JsonResponse(['message' => 'Invalid credentials.'], Response::HTTP_UNAUTHORIZED);
        }

        // 2. Verify password
        if (!$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['message' => 'Invalid credentials.'], Response::HTTP_UNAUTHORIZED);
        }

        // 3. Authentication successful: Set up session
        // Invalidate old session ID to prevent session fixation attacks and start a new one.
        $session->invalidate(); 
        $session->start();      

        // Store user UUID in the session. Ensure it's stored as a string.
        $session->set('user_uuid', $user->getUuid()->toRfc4122()); 

        return new JsonResponse([
            'message' => 'Login successful!',
            'user_uuid' => $user->getUuid()->toRfc4122(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles()
        ], Response::HTTP_OK);
    }

    /**
     * Handles user logout by clearing and destroying the session.
     *
     * @param SessionInterface $session Symfony's session service.
     * @param TokenStorageInterface $tokenStorage Symfony's token storage service for security context.
     * @return JsonResponse A JSON response indicating logout status.
     */
    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])] // POST is generally safer for logout
    public function logout(SessionInterface $session, TokenStorageInterface $tokenStorage): JsonResponse
    {
        // Clear the security token from the current session
        $tokenStorage->setToken(null);
        
        // Clear all session data
        $session->clear();
        
        // Invalidate the session ID (makes the old session ID unusable)
        $session->invalidate();
        
        // Ensure all session data is purged, especially if relying on session bags
        $session->getBag('attributes')->clear();
        $session->getBag('flashes')->clear();

        return new JsonResponse(['message' => 'Logged out successfully!'], Response::HTTP_OK);
    }
}
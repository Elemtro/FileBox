<?php
// src/Controller/RegistrationController.php

namespace App\Controller;

use App\Entity\User;
// REMOVED: use App\Repository\UserRepository; // No longer needed for this approach
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


class RegistrationController extends AbstractController
{
    /**
     * Handles user registration.
     *
     * @param Request $request The incoming HTTP request, expecting JSON payload.
     * @param UserPasswordHasherInterface $passwordHasher Service to hash user passwords.
     * @param EntityManagerInterface $entityManager Doctrine's entity manager for persisting data.
     * @return JsonResponse A JSON response indicating success or failure.
     */
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Array to collect validation errors
        $errorMessages = [];

        // --- MINIMAL MANUAL VALIDATION CHECKS (ONLY presence and basic email format) ---
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        // Basic check for email presence
        if ($email === null || trim($email) === '') {
            $errorMessages[] = 'Email is required.';
        } else if (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) { // Manual email format validation
            $errorMessages[] = 'The email "' . htmlspecialchars(trim($email)) . '" is not a valid email address.';
        }

        // Basic check for password presence
        if ($password === null || trim($password) === '') {
            $errorMessages[] = 'Password is required.';
        }

        // If any manual errors, return them (these are now only for presence and format)
        if (count($errorMessages) > 0) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $errorMessages], JsonResponse::HTTP_BAD_REQUEST);
        }
        // --- END MINIMAL MANUAL VALIDATION CHECKS ---

        $user = new User();
        $user->setEmail(trim($email));
        $plainPassword = trim($password);

        try {
            // Hash the password
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plainPassword
            );

            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            return new JsonResponse(['message' => 'User registered successfully!'], JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            // This catch block will now catch unique email violations as a database exception,
            // resulting in a 500 Internal Server Error.
            // You can add more specific error handling here if needed (e.g., check for specific SQLState codes)
            return new JsonResponse(['message' => 'An error occurred during registration.', 'error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
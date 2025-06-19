<?php
// src/Service/RegistrationService.php
namespace App\Api\Service;


use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use App\Api\Dto\LoginRequest;
use App\Storage\Repository\UserRepository;
use App\Storage\Entity\User;
use App\Api\Dto\RegistrationRequest;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;


class AuthService
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $repository,
        private readonly RequestStack $requestStack,
    ) {}

    public function login(LoginRequest $dto): User
    {
        $session = $this->getSession();
        $user = $this->repository->findOneByEmail($dto->email);
        if (!$user || !$this->passwordHasher->isPasswordValid($user, $dto->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        $session->invalidate();
        $session->start();
        $session->set('user_uuid', $user->getUuid());

        return $user;
    }

    public function register(RegistrationRequest $dto): User
    {
        $session = $this->getSession();
        $user = new User();
        $user->setEmail($dto->email);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $dto->password)
        );
        try {
            $this->repository->saveUser($user);
        } catch (UniqueConstraintViolationException $e) {
            throw new \RuntimeException('User already exists');
        } catch (\Exception $e) {
            throw $e;
        }

        return $user;
    }
    public function getSession(): ?SessionInterface
    {
        return $this->requestStack->getSession();
    }
    public function logout()
    {
        $session = $this->getSession();

        if ($session !== null) {
            $session->clear();
            $session->invalidate();
            $session->getBag('attributes')->clear();
            $session->getBag('flashes')->clear();
        }
    }
}

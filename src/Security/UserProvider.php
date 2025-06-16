<?php
    // src/Security/UserProvider.php

    namespace App\Security;

    use App\Entity\User;
    use App\Repository\UserRepository;
    use Symfony\Component\Security\Core\User\UserProviderInterface;
    use Symfony\Component\Security\Core\User\UserInterface;
    use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
    use Symfony\Component\Security\Core\Exception\UserNotFoundException;
    use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
    use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
    use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
    use Symfony\Component\Uid\Uuid; // Needed for Uuid::fromString()

    class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
    {
        private UserRepository $userRepository;
        private ?UserPasswordHasherInterface $passwordHasher;

        public function __construct(UserRepository $userRepository, ?UserPasswordHasherInterface $passwordHasher = null)
        {
            $this->userRepository = $userRepository;
            $this->passwordHasher = $passwordHasher;
        }

        public function refreshUser(UserInterface $user): UserInterface
        {
            if (!$user instanceof User) {
                throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_debug_type($user)));
            }

            // REFRESH BY UUID STRING: Get the UUID as a string from the User entity
            // The getUuid() method in User entity now returns a Uuid object if valid,
            // then we convert it back to string for the findOneByUuid method.
            $reloadedUser = $this->userRepository->findOneByUuid($user->getUuid()->toRfc4122());

            if (null === $reloadedUser) {
                throw new UserNotFoundException(sprintf('User with identifier "%s" not found.', $user->getUserIdentifier()));
            }

            return $reloadedUser;
        }

        public function supportsClass(string $class): bool
        {
            return User::class === $class || is_subclass_of($class, User::class);
        }

        public function loadUserByIdentifier(string $identifier): UserInterface
        {
            // Load user by email, as before
            $user = $this->userRepository->findOneByEmail($identifier);

            if (null === $user) {
                throw new UserNotFoundException(sprintf('User with identifier "%s" not found.', $identifier));
            }

            return $user;
        }

        public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
        {
            if (!$user instanceof User || null === $this->passwordHasher) {
                return;
            }

            $user->setPassword($newHashedPassword);
            $this->userRepository->getEntityManager()->persist($user);
            $this->userRepository->getEntityManager()->flush();
        }
    }
    
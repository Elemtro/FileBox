<?php
    // src/Repository/UserRepository.php

    namespace App\Repository;

    use App\Entity\User;
    use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
    use Doctrine\Persistence\ManagerRegistry;
    use Symfony\Component\Uid\Uuid; // Still needed for Uuid::fromString()

    /**
     * @extends ServiceEntityRepository<User>
     *
     * This class provides methods to interact with the User entity in the database.
     *
     * @method User|null find($id, $lockMode = null, $lockVersion = null)
     * @method User|null findOneBy(array $criteria, array $orderBy = null)
     * @method User[]    findAll()
     * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
     */
    class UserRepository extends ServiceEntityRepository
    {
        public function __construct(ManagerRegistry $registry)
        {
            parent::__construct($registry, User::class);
        }

        /**
         * Finds a User by their email address.
         *
         * @param string $email The email address to search for.
         * @return User|null The User entity if found, otherwise null.
         */
        public function findOneByEmail(string $email): ?User
        {
            return $this->createQueryBuilder('u')
                ->andWhere('u.email = :val')
                ->setParameter('val', $email)
                ->getQuery()
                ->getOneOrNullResult();
        }

        /**
         * Finds a User by their UUID string.
         *
         * @param string $uuidString The UUID string to search for.
         * @return User|null The User entity if found, otherwise null.
         */
        public function findOneByUuid(string $uuidString): ?User
        {
            // Convert string to Uuid object before passing to find() if find() expects Uuid object
            // If Doctrine's UuidType is correctly set up, find() might handle string directly.
            // But explicitly converting is safer with string property type.
            try {
                $uuidObject = Uuid::fromString($uuidString);
                return $this->find($uuidObject);
            } catch (\InvalidArgumentException $e) {
                // Log invalid UUID string if it occurs
                // $this->logger->error('Invalid UUID string in findOneByUuid: ' . $uuidString);
                return null;
            }
        }
    }
    
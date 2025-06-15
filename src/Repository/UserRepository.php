<?php

namespace App\Repository;

use App\Entity\File;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class UserRepository extends ServiceEntityRepository 
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }
    /**
     * Finds a User by their email address.
     * This is a common custom method you might add to a UserRepository.
     *
     * @param string $email The email address to search for.
     * @return User|null The User entity if found, otherwise null.
     */
    public function findOneByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u') // 'u' is an alias for the User entity
            ->andWhere('u.email = :val')     // Add a WHERE clause for the email field
            ->setParameter('val', $email)    // Set the value for the ':val' parameter
            ->getQuery()                     // Get the Doctrine Query object
            ->getOneOrNullResult();          // Execute the query and get one result or null
    }
    /**
     * Finds a User by their UUID.
     * While find() works with UUIDs, a dedicated method can improve clarity.
     *
     * @param Uuid $uuid The UUID object to search for.
     * @return User|null The User entity if found, otherwise null.
     */
    public function findOneByUuid(Uuid $uuid): ?User
    {
        // Doctrine's find() method can often handle primary keys directly.
        // For UUIDs, ensure your DBAL setup is correct.
        return $this->find($uuid);
    }
}
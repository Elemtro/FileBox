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
     * Finds files uploaded by a specific user using Doctrine Query Builder (DQL).
     *
     * @param User $user The User entity whose files to retrieve.
     * @return File[] Returns an array of File objects.
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('f') // Alias 'f' for the File entity
            ->andWhere('f.user = :user')     // Filter by the 'user' relationship (entity property)
            ->setParameter('user', $user)    // Pass the User entity object directly as a parameter
            ->orderBy('f.uploadedAt', 'DESC') // Order results by 'uploadedAt' property in descending order
            ->getQuery()                     // Get the DQL Query object
            ->getResult();                   // Execute the query and get all results
    }

    /**
     * Finds a specific file by its original filename for a given user using DQL.
     * This leverages the unique constraint defined in the File entity.
     *
     * @param string $originalFilename The original filename of the file.
     * @param User $user The User entity who owns the file.
     * @return File|null The File entity if found, otherwise null.
     */
    public function findOneByOriginalFilenameAndUser(string $originalFilename, User $user): ?File
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.originalFilename = :filename') // Filter by 'originalFilename' property
            ->andWhere('f.user = :user')                 // Filter by 'user' relationship
            ->setParameter('filename', $originalFilename) // Bind filename value
            ->setParameter('user', $user)                // Bind User entity object
            ->getQuery()
            ->getOneOrNullResult();
    }
}
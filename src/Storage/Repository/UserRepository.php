<?php
namespace App\Storage\Repository;

use App\Storage\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class UserRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, User::class);
    }

    public function saveUser(User $user): User
    {
        $entityManager = $this->getEntityManager();
        
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    public function findOneByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :val')
            ->setParameter('val', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByUuid(string $uuidString): ?User
    {
        try {
            $uuidObject = Uuid::fromString($uuidString);
            return $this->find($uuidObject);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }
}

<?php

namespace App\Storage\Repository;

use App\Storage\Entity\File;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Storage\Entity\User;

class FileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }
    public function findAllUserFiles($user)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
    public function saveFile($fileData, User $user): File
    {
        $fileEntity = new File();

        $fileEntity->setUser($user)
            ->setOriginalFilename($fileData['originalFilename'])
            ->setSize($fileData['size'])
            ->setMimeType($fileData['mimeType'])
            ->setStoragePath($fileData['storagePath'])
            ->setUploadedAt(new \DateTimeImmutable());
        $entityManager = $this->getEntityManager();

        $entityManager->persist($fileEntity);
        $entityManager->flush();

        return $fileEntity;
    }
    public function findOneByFileUuid(string $fileUuid)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.fileUuid = :uuid')
            ->setParameter('uuid', $fileUuid)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function deleteFile($file){
        $entityManager = $this->getEntityManager();
        
        $entityManager->remove($file);
        $entityManager->flush();

    }
}

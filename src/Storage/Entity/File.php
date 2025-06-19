<?php
// src/Entity/File.php

namespace App\Storage\Entity;

use App\Storage\Repository\FileRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: FileRepository::class)]
#[ORM\Table(name: 'file')]
class File
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID, unique: true)] 
    private ?string $fileUuid = null;

    #[ORM\ManyToOne(targetEntity: User::class)] 
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'uuid')] 
    private ?User $user = null; 

    #[ORM\Column(length: 255)]
    private ?string $originalFilename = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?int $size = null;

    #[ORM\Column(length: 255)]
    private ?string $mimeType = null;

    #[ORM\Column(length: 255, unique: true)] 
    private ?string $storagePath = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $uploadedAt = null;

    public function __construct()
    {
        $this->fileUuid = Uuid::v4()->toRfc4122();
        $this->uploadedAt = new \DateTimeImmutable();
    }
    public function getfileUuid(): ?string
    {
        return $this->fileUuid;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }


    public function getSize(): ?int
    {
        return $this->size;
    }


    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }


    public function getStoragePath(): ?string
    {
        return $this->storagePath;
    }


    public function getUploadedAt(): ?\DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function setOriginalFilename(string $originalFilename): static
    {
        $this->originalFilename = $originalFilename;
        return $this;
    }

    public function setSize(int $size): static
    {
        $this->size = $size;
        return $this;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    public function setStoragePath(string $storagePath): static
    {
        $this->storagePath = $storagePath;
        return $this;
    }

    public function setUploadedAt(\DateTimeImmutable $uploadedAt): static
    {
        $this->uploadedAt = $uploadedAt;
        return $this;
    }
}

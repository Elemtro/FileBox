<?php
// src/Entity/File.php

namespace App\Storage\Entity;

use App\Repository\FileRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Doctrine\DBAL\Types\Types;
// NO Validation Annotations or their use statements here
// REMOVED: use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
// REMOVED: use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FileRepository::class)]
#[ORM\Table(name: 'file')]
// REMOVED: #[UniqueEntity(...)]
class File
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID, unique: true)] // Primary key: file_uuid
    private ?Uuid $fileUuid = null;

    #[ORM\ManyToOne(targetEntity: User::class)] // Many-to-one relationship with User
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'uuid')] // Correct foreign key reference
    private ?User $user = null; // This will hold the User entity

    #[ORM\Column(length: 255)]
    private ?string $originalFilename = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?int $size = null;

    #[ORM\Column(length: 255)]
    private ?string $mimeType = null;

    #[ORM\Column(length: 255, unique: true)] // Database-level unique constraint
    private ?string $storagePath = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $uploadedAt = null;

    public function __construct()
    {
        $this->fileUuid = Uuid::v4();
        $this->uploadedAt = new \DateTimeImmutable();
    }

    public function getFileUuid(): ?Uuid
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

    public function setOriginalFilename(string $originalFilename): static
    {
        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): static
    {
        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        return $this;
    }

    public function getStoragePath(): ?string
    {
        return $this->storagePath;
    }

    public function setStoragePath(string $storagePath): static
    {
        return $this;
    }

    public function getUploadedAt(): ?\DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeImmutable $uploadedAt): static
    {
        return $this;
    }
}
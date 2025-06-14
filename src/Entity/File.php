<?php
// src/Entity/File.php

namespace App\Entity;

use App\Repository\FileRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Doctrine\DBAL\Types\Types; // Import Types for 'uuid' and 'datetime_immutable' mapping
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert; // For validation constraints

#[ORM\Entity(repositoryClass: FileRepository::class)]
#[ORM\Table(name: 'file')]
#[UniqueEntity(fields: ['originalFilename', 'user'], message: 'A file with this name already exists for this user.')]
class File
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID, unique: true)] // Primary key: file_uuid
    private ?Uuid $fileUuid = null;

    #[ORM\ManyToOne(targetEntity: User::class)] // Many-to-one relationship with User
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'uuid')] // <<<--- ADD THIS PART
    private ?User $user = null; // This will hold the User entity, Doctrine handles the user_uuid foreign key

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Original filename cannot be blank.')]
    #[Assert\Length(max: 255, maxMessage: 'Original filename cannot be longer than {{ limit }} characters.')]
    private ?string $originalFilename = null;

    #[ORM\Column(type: Types::BIGINT)] // Use BIGINT for file size to accommodate large files
    #[Assert\NotBlank(message: 'File size cannot be blank.')]
    #[Assert\PositiveOrZero(message: 'File size must be a positive number or zero.')]
    private ?int $size = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'MIME type cannot be blank.')]
    #[Assert\Length(max: 255, maxMessage: 'MIME type cannot be longer than {{ limit }} characters.')]
    private ?string $mimeType = null; // Suggested: Stores the file's MIME type (e.g., 'image/jpeg')

    #[ORM\Column(length: 255, unique: true)] // Suggested: Path where the file is physically stored
    #[Assert\NotBlank(message: 'Storage path cannot be blank.')]
    #[Assert\Length(max: 255, maxMessage: 'Storage path cannot be longer than {{ limit }} characters.')]
    private ?string $storagePath = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)] // Suggested: Timestamp of when the file was uploaded
    private ?\DateTimeImmutable $uploadedAt = null;

    public function __construct()
    {
        $this->fileUuid = Uuid::v4(); // Generate a new UUID for the file
        $this->uploadedAt = new \DateTimeImmutable(); // Set the upload timestamp
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
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getStoragePath(): ?string
    {
        return $this->storagePath;
    }

    public function setStoragePath(string $storagePath): static
    {
        $this->storagePath = $storagePath;

        return $this;
    }

    public function getUploadedAt(): ?\DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeImmutable $uploadedAt): static
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }
}

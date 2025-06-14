<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types; // Import Types for 'uuid' and 'datetime_immutable' mapping
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity; // Needed for unique email validation

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email.')] // Added for unique email constraint
class User
{
    #[ORM\Id]
    // Removed #[ORM\GeneratedValue] as UUIDs are generated manually in the constructor
    #[ORM\Column(type: Types::GUID, unique: true)] // Correct Doctrine type for UUID
    private ?Uuid $uuid = null;

    // The 'ID' property was removed as 'uuid' serves as the primary key.

    #[ORM\Column(length: 70, unique: true)] // Added unique: true for the email to enforce uniqueness at the database level
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)] // Correct Doctrine type for DateTimeImmutable objects
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->uuid = Uuid::v4(); // Automatically generate a UUID when a new User object is created
        $this->createdAt = new \DateTimeImmutable(); // Set the creation timestamp
    }

    public function getUuid(): ?Uuid // Renamed from getId() to getUuid() to reflect the UUID primary key
    {
        return $this->uuid;
    }

    // The setID() method was removed as the ID property is no longer present.

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable // Renamed from getDateTimeImmutable() to getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static // Added the missing setter for createdAt
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}

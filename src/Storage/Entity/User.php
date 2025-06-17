<?php
// src/Entity/User.php

namespace App\Storage\Entity;

use App\Enum\UserRole;
use App\Storage\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID, unique: true)]
    private ?string $uuid = null;

    #[ORM\Column(length: 70, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(enumType: UserRole::class, type: Types::STRING)]
    private UserRole $role = UserRole::USER;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->uuid = Uuid::v4()->toRfc4122();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getUuid(): ?Uuid
    {
        return is_string($this->uuid) ? Uuid::fromString($this->uuid) : null;
    }


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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role): void
    {
        $this->role = $role;
    }

    public function getRoles(): array
    {
        return [$this->role->value];
    }

    public function eraseCredentials(): void {}
}

<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
// REMOVED: #[UniqueEntity(fields: ['email'], message: 'There is already an account with this email.')] // Removed again to simplify
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID, unique: true)]
    // CHANGED: Property type to string to bypass hydration TypeError
    private ?string $uuid = null; 

    #[ORM\Column(length: 70, unique: true)]
    #[Assert\NotBlank(message: 'Email cannot be blank.')]
    #[Assert\Email(message: 'The email "{{ value }}" is not a valid email address.')]
    #[Assert\Length(max: 70, maxMessage: 'Your email cannot be longer than {{ limit }} characters.')]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Password cannot be blank.')]
    #[Assert\Length(min: 8, max: 255, minMessage: 'Your password must be at least {{ limit }} characters long.')]
    private ?string $password = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        // CHANGED: Store UUID as its string representation
        $this->uuid = Uuid::v4()->toRfc4122(); 
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getUuid(): ?Uuid
    {
        // NOW THE PRIMARY WAY TO GET A Uuid OBJECT: Convert the stored string to Uuid object
        if (is_string($this->uuid)) {
            try {
                return Uuid::fromString($this->uuid);
            } catch (\InvalidArgumentException $e) {
                // Log the error: $this->logger->error('Invalid UUID string from DB: ' . $this->uuid);
                return null; 
            }
        }
        return null; // If $this->uuid is null or not a string, return null Uuid
    }

    /**
     * Set the UUID property. Only accepts string or null.
     * Use this with caution, primarily for hydration by Doctrine.
     *
     * @param string|null $uuid
     * @return static
     */
    public function setUuid(?string $uuid): static
    {
        $this->uuid = $uuid;
        return $this;
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

    /**
     * @see PasswordAuthenticatedUserInterface
     */
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
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     * @return string[]
     */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // Clear sensitive data (like plain password if it were stored temporarily)
    }
}
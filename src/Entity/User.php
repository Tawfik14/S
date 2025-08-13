<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
#[UniqueEntity(fields: ['pseudo'], message: 'Ce pseudo est déjà utilisé.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // --- PSEUDO (nouveau) ---
    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: 'Veuillez entrer un pseudo.')]
    #[Assert\Length(min: 3, max: 50)]
    private ?string $pseudo = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Veuillez entrer votre prénom.')]
    private string $firstName = '';

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Veuillez entrer votre nom.')]
    private string $lastName = '';

    #[ORM\Column(type: 'date')]
    #[Assert\NotBlank(message: 'Veuillez entrer votre date de naissance.')]
    #[Assert\LessThanOrEqual('today', message: 'La date de naissance doit être dans le passé.')]
    private ?DateTimeInterface $birthDate = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Veuillez entrer un email.')]
    #[Assert\Email(message: 'Email invalide.')]
    private string $email = '';

    #[ORM\Column(type: 'json')]
    private array $roles = []; // ne pas exposer dans le formulaire

    #[ORM\Column]
    private string $password = '';

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    // Pseudo
    public function getPseudo(): ?string { return $this->pseudo; }
    public function setPseudo(string $pseudo): self { $this->pseudo = $pseudo; return $this; }

    // Prénom
    public function getFirstName(): string { return $this->firstName; }
    public function setFirstName(string $firstName): self { $this->firstName = $firstName; return $this; }

    // Nom
    public function getLastName(): string { return $this->lastName; }
    public function setLastName(string $lastName): self { $this->lastName = $lastName; return $this; }

    // Naissance
    public function getBirthDate(): ?DateTimeInterface { return $this->birthDate; }
    public function setBirthDate(?DateTimeInterface $birthDate): self { $this->birthDate = $birthDate; return $this; }

    // Email / identifier
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): self { $this->email = strtolower(trim($email)); return $this; }
    public function getUserIdentifier(): string { return $this->email; }
    /** @deprecated use getUserIdentifier() */ public function getUsername(): string { return $this->getUserIdentifier(); }

    // Rôles
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER'; // toujours au minimum
        return array_values(array_unique($roles));
    }
    public function setRoles(array $roles): self { $this->roles = array_values(array_unique($roles)); return $this; }

    // Mot de passe (hashé)
    public function getPassword(): string { return $this->password; }
    public function setPassword(string $hashedPassword): self { $this->password = $hashedPassword; return $this; }

    public function eraseCredentials(): void { /* no-op */ }

    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
}


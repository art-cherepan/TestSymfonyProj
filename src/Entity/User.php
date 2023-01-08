<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: 'email', message: 'У вас уже есть аккаунт')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_USER = 'ROLE_USER';

    public function __construct()
    {
        $this->roles = [self::ROLE_USER];
        $this->enabled = false;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank()]
    #[Assert\Email()]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $password = null;

    #[Assert\NotBlank()]
    private $plainPassword;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $confirmationCode;

    #[ORM\Column(type: 'boolean')]
    private $enabled;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        return [
            'ROLE_USER'
        ];
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getConfirmationCode(): string
    {
        return $this->confirmationCode;
    }

    public function setConfirmationCode(string $confirmationCode): self
    {
        $this->confirmationCode = $confirmationCode;

        return $this;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnable(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        return $this->email;
    }
}

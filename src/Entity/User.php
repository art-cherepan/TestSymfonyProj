<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
Use DateTimeInterface;
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
    public const GITHUB_OAUTH = 'Github';
    public const GOOGLE_OAUTH = 'Google';

    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    public function __construct(
      $clientId,
      string $email,
      string $username,
      string $oauthType,
      array $roles,
    ) {
        $this->clientId = $clientId;
        $this->email = $email;
        $this->username = $username;
        $this->oauthType = $oauthType;
        $this->lastLogin = new DateTime('now');
        $this->roles = $roles;
        $this->enabled = false;
    }

    public static function fromGithubRequest(
        int $clientId,
        string $email,
        string $username
    ): User
    {
        return new self(
          $clientId,
          $email,
          $username,
          self::GITHUB_OAUTH,
          [self::ROLE_USER]
        );
    }

    public static function fromGoogleRequest(
        string $clientId,
        string $email,
        string $username
    ): User
    {
        return new self(
            $clientId,
            $email,
            $username,
            self::GOOGLE_OAUTH,
            [self::ROLE_USER]
        );
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    private ?int $clientId = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank()]
    #[Assert\Email()]
    private ?string $email = null;

    #[ORM\Column(type: 'string')]
    private ?string $username = null;

    #[ORM\Column(type: 'string')]
    private ?string $oauthType = null;

    #[ORM\Column(type: 'datetime')]
    private DateTimeInterface $lastLogin;

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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        return [
            'ROLE_USER'
        ];
    }

    public function getConfirmationCode(): string
    {
        return $this->confirmationCode;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function getOauthType(): string
    {
        return $this->oauthType;
    }

    public function getLastLogin(): DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }
}

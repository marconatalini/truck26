<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity('username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(type: "json", options:['jsonb' => true] )]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, DriverLog>
     */
    #[ORM\OneToMany(targetEntity: DriverLog::class, mappedBy: 'driver')]
    private Collection $driverLogs;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $firedAt = null;

    public function __construct()
    {
        $this->driverLogs = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->username;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

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

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    /**
     * @return Collection<int, DriverLog>
     */
    public function getDriverLogs(): Collection
    {
        return $this->driverLogs;
    }

    public function addDriverLog(DriverLog $driverLog): static
    {
        if (!$this->driverLogs->contains($driverLog)) {
            $this->driverLogs->add($driverLog);
            $driverLog->setDriver($this);
        }

        return $this;
    }

    public function removeDriverLog(DriverLog $driverLog): static
    {
        if ($this->driverLogs->removeElement($driverLog)) {
            // set the owning side to null (unless already changed)
            if ($driverLog->getDriver() === $this) {
                $driverLog->setDriver(null);
            }
        }

        return $this;
    }

    public function getFiredAt(): ?\DateTimeImmutable
    {
        return $this->firedAt;
    }

    public function setFiredAt(?\DateTimeImmutable $firedAt): static
    {
        $this->firedAt = $firedAt;

        return $this;
    }
}

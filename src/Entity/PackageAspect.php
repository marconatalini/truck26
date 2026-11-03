<?php

namespace App\Entity;

use App\Repository\PackageDefaultRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: PackageDefaultRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_PACKAGE_ASPECT', fields: ['aspect'])]
#[UniqueEntity(fields: ['aspect'])]
class PackageAspect
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $aspect = null;

    #[ORM\Column]
    private ?int $length = null;

    #[ORM\Column]
    private ?int $width = null;

    #[ORM\Column]
    private ?int $height = null;

    #[ORM\Column]
    private ?int $weight = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $scope = null;

    public function __toString(): string
    {
        return $this->aspect;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAspect(): ?string
    {
        return $this->aspect;
    }

    public function setAspect(string $aspect): static
    {
        $this->aspect = $aspect;

        return $this;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(int $length): static
    {
        $this->length = $length;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(int $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(int $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(?string $scope): static
    {
        $this->scope = $scope;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\PackageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PackageRepository::class)]
class Package
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column()]
    private ?int $length = 0;

    #[ORM\Column]
    private ?int $width = 0;

    #[ORM\Column]
    private ?int $height = 0;

    #[ORM\Column]
    private ?int $weight = 0;

    #[ORM\ManyToOne(inversedBy: 'packages')]
    private ?Mission $mission = null;

    #[ORM\Column]
    private int $quantity = 1;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?PackageAspect $aspect = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(int $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }
    
    public function getMission(): ?Mission
    {
        return $this->mission;
    }

    public function setMission(?Mission $mission): self
    {
        $this->mission = $mission;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function __toString(): string
    {
        return sprintf('%d %s da %sKg (%d x %d x %d)', $this->quantity, $this->aspect, $this->weight, $this->length, $this->width, $this->height);
    }

    public function getAspect(): ?PackageAspect
    {
        return $this->aspect;
    }

    public function setAspect(?PackageAspect $aspect): static
    {
        $this->aspect = $aspect;

        return $this;
    }


}

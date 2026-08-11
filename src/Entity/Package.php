<?php

namespace App\Entity;

use App\Repository\PackageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PackageRepository::class)]
class Package
{
    public const ASPECTS = ['bancale', 'scatola', 'gabbia', 'fascio'];
    public const DEFAULTS = [
        'bancale' => '1200x800x1000x300',
        'scatola' => '300x500x400x25',
        'gabbia' => '1000x1000x1000x500',
        'fascio' => '100x2500x100x20'];

    public function setDefaults(string $aspect): void
    {
        $this->aspect = $aspect;
        $this->width = explode("x", self::DEFAULTS[$aspect])[0];
        $this->length = explode("x", self::DEFAULTS[$aspect])[1];
        $this->height = explode("x", self::DEFAULTS[$aspect])[2];
        $this->weight = explode("x", self::DEFAULTS[$aspect])[3];
    }

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

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $aspect = null;

    #[ORM\ManyToOne(inversedBy: 'packages')]
    private ?Mission $mission = null;

    #[ORM\Column]
    private int $quantity = 1;

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

    public function getAspect(): ?string
    {
        return $this->aspect;
    }

    public function setAspect(?string $aspect): self
    {
        $this->aspect = $aspect;

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


}

<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_ADDRESS', fields: ['addressLine1', 'city', 'province'])]
#[UniqueEntity(fields: ['addressLine1', 'city', 'province'],
    message: 'address.not.unique', errorPath: 'city'
)]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressLine1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressLine2 = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $province = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isPrimary = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updateAt = null;

    #[ORM\Column(length: 40, nullable: true, columnDefinition: "POINT")]
    private ?string $coordinates = null;

    public function __toString(): string
    {
        return $this->addressLine1;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddressLine1(): ?string
    {
        return $this->addressLine1;
    }

    public function setAddressLine1(?string $addressLine1): static
    {
        $this->addressLine1 = $addressLine1;

        return $this;
    }

    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    public function setAddressLine2(?string $addressLine2): static
    {
        $this->addressLine2 = $addressLine2;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getProvince(): ?string
    {
        return $this->province;
    }

    public function setProvince(?string $province): static
    {
        $this->province = $province;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function isPrimary(): ?bool
    {
        return $this->isPrimary;
    }

    public function setIsPrimary(?bool $isPrimary): static
    {
        $this->isPrimary = $isPrimary;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeImmutable
    {
        return $this->updateAt;
    }

    public function setUpdateAt(?\DateTimeImmutable $updateAt): static
    {
        $this->updateAt = $updateAt;

        return $this;
    }

    public function getCoordinatesArray(): ?array
    {
        if (!$this->coordinates) return null;

        // PostgreSQL restituisce il POINT come stringa "(12.345,45.678)"
        $coords = str_replace(['(', ')'], '', $this->coordinates);
        $parts = explode(',', $coords);

        return [
            'lat' => (float) $parts[0],
            'lng' => (float) $parts[1],
        ];
    }

    public function getCoordinates(): ?string
    {
        return $this->coordinates;
    }


    public function setCoordinates(?string $coordinates): static
    {
        // Se ricevi una stringa tipo "41.8902, 12.4922" dal form
        if (is_string($coordinates)) {
            // Pulizia minima: togliamo spazi e assicuriamoci che abbia le parentesi per Postgres
            $coords = trim($coordinates);
            if (strpos($coords, '(') === false) {
                $coords = "(" . str_replace(' ', '', $coords) . ")";
            }
            $this->coordinates = $coords;
        } else {
            $this->coordinates = $coordinates;
        }

        return $this;
    }
}

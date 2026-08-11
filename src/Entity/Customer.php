<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $email = null;


    #[ORM\Column(length: 11)]
    private ?string $vat_number = null;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $invoice_note = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $note = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $pec = null;

    #[ORM\Column(length: 7, nullable: true)]
    private ?string $sdi = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $bank_charge = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Address $address = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }



    public function __toString(): string
    {
        return $this->name;
    }


    /**
     * Fake function for OneToOne Place relations
     */
    /*public function getPlaceAddress(): array
    {
        return $this->place ? [$this->place] : [];
    }

    public function setPlaceAddress(array $places): self
    {
        $this->place = array_shift($places);
        return $this;
    }*/




    public function getVatNumber(): ?string
    {
        return $this->vat_number;
    }

    public function setVatNumber(string $vat_number): static
    {
        $this->vat_number = $vat_number;

        return $this;
    }

    

    public function getInvoiceNote(): ?string
    {
        return $this->invoice_note;
    }

    public function setInvoiceNote(?string $invoice_note): static
    {
        $this->invoice_note = $invoice_note;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getPec(): ?string
    {
        return $this->pec;
    }

    public function setPec(?string $pec): static
    {
        $this->pec = $pec;

        return $this;
    }

    public function getSdi(): ?string
    {
        return $this->sdi;
    }

    public function setSdi(?string $sdi): static
    {
        $this->sdi = $sdi;

        return $this;
    }

    public function getBankCharge(): ?string
    {
        return $this->bank_charge;
    }

    public function setBankCharge(?string $bank_charge): static
    {
        $this->bank_charge = $bank_charge;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): static
    {
        $this->address = $address;

        return $this;
    }

}

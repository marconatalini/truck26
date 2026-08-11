<?php

namespace App\Entity;

use App\Repository\VehicleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleRepository::class)]
class Vehicle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 7)]
    private ?string $plate = null;

    #[ORM\Column]
    private ?int $length = 0;

    #[ORM\Column]
    private ?int $width = 0;

    #[ORM\Column]
    private ?int $height = 0;

    #[ORM\Column]
    private ?int $weight_range = 0;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $color = null;


    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $purchase_at = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $next_tax_at = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $next_inspection_at = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $next_tachograph_at = null;

    #[ORM\Column]
    private ?bool $is_available = null;

    #[ORM\Column(length: 30)]
    private ?string $model = null;

    /**
     * @var Collection<int, DriverLog>
     */
    #[ORM\OneToMany(targetEntity: DriverLog::class, mappedBy: 'vehicle')]
    private Collection $driverLogs;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $note = null;

    public function __construct()
    {
        $this->driverLogs = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlate(): ?string
    {
        return $this->plate;
    }

    public function setPlate(string $plate): self
    {
        $this->plate = $plate;

        return $this;
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

    public function getWeightRange(): ?int
    {
        return $this->weight_range;
    }

    public function setWeightRange(int $weight_range): self
    {
        $this->weight_range = $weight_range;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function __toString(): string
    {
        return $this->plate;
    }


    public function getPurchaseAt(): ?\DateTimeImmutable
    {
        return $this->purchase_at;
    }

    public function setPurchaseAt(\DateTimeImmutable $purchase_at): static
    {
        $this->purchase_at = $purchase_at;

        return $this;
    }

    public function getNextTaxAt(): ?\DateTimeImmutable
    {
        return $this->next_tax_at;
    }

    public function setNextTaxAt(\DateTimeImmutable $next_tax_at): static
    {
        $this->next_tax_at = $next_tax_at;

        return $this;
    }

    public function getNextInspectionAt(): ?\DateTimeImmutable
    {
        return $this->next_inspection_at;
    }

    public function setNextInspectionAt(\DateTimeImmutable $next_inspection_at): static
    {
        $this->next_inspection_at = $next_inspection_at;

        return $this;
    }

    public function getNextTachographAt(): ?\DateTimeImmutable
    {
        return $this->next_tachograph_at;
    }

    public function setNextTachographAt(?\DateTimeImmutable $next_tachograph_at): static
    {
        $this->next_tachograph_at = $next_tachograph_at;

        return $this;
    }

    public function isIsAvailable(): ?bool
    {
        return $this->is_available;
    }

    public function setIsAvailable(bool $is_available): static
    {
        $this->is_available = $is_available;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
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
            $driverLog->setVehicle($this);
        }

        return $this;
    }

    public function removeDriverLog(DriverLog $driverLog): static
    {
        if ($this->driverLogs->removeElement($driverLog)) {
            // set the owning side to null (unless already changed)
            if ($driverLog->getVehicle() === $this) {
                $driverLog->setVehicle(null);
            }
        }

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

}

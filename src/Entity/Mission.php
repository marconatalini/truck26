<?php

namespace App\Entity;

use App\Repository\MissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

# TODO collegamento con richiesta/preventivo
# TODO numero DDT per ricerca storia e controllo inserimento prezzi DDT

#[ORM\Entity(repositoryClass: MissionRepository::class)]
class Mission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $ddt_reference = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $available_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $pickup_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $delivered_before_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $delivery_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $scheduled_at = null;
    
    #[ORM\Column]
    private ?\DateTimeImmutable $create_at;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    #[ORM\Column]
    private ?int $weight = 0;

    #[ORM\Column]
    private ?int $distance = 0;

    #[ORM\Column]
    private ?int $price = 0;

    #[ORM\OneToMany(targetEntity: Package::class, mappedBy: 'mission', cascade: ['persist', 'remove'])]
    #[Assert\Valid()]
    private Collection $packages;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull()]
    private ?Place $pickupPlace = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull()]
    private ?Place $deliveryPlace = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column]
    private ?bool $picked = false;

    #[ORM\Column]
    private ?bool $delivered = false;

    #[ORM\ManyToOne]
    private ?Vehicle $pickingVehicle = null;

    #[ORM\ManyToOne]
    private ?Vehicle $deliveringVehicle = null;

    /**
     * @var Collection<int, MediaUpload>
     */
    #[ORM\OneToMany(targetEntity: MediaUpload::class, mappedBy: 'mission', cascade: ['persist', 'remove'])]
    private Collection $documents;

    /**
     * @var Collection<int, PictureUpload>
     */
    #[ORM\OneToMany(targetEntity: PictureUpload::class, mappedBy: 'mission', cascade: ['persist', 'remove'])]
    private Collection $pictures;

    #[ORM\Column]
    private ?bool $express = false;

    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 1)]
    private ?string $area = '0.0';

    public function __construct()
    {
        $this->packages = new ArrayCollection();
        $this->create_at = new \DateTimeImmutable();
        $this->documents = new ArrayCollection();
        $this->pictures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPickupAt(): ?\DateTimeImmutable
    {
        return $this->pickup_at;
    }

    public function setPickupAt(?\DateTimeImmutable $pickup_at): self
    {
        $this->pickup_at = $pickup_at;

        return $this;
    }

    public function getDeliveryAt(): ?\DateTimeImmutable
    {
        return $this->delivery_at;
    }

    public function setDeliveryAt(?\DateTimeImmutable $delivery_at): self
    {
        $this->delivery_at = $delivery_at;

        return $this;
    }

    public function getScheduledAt(): ?\DateTimeImmutable
    {
        return $this->scheduled_at;
    }

    public function setScheduledAt(?\DateTimeImmutable $scheduled_at): self
    {
        $this->scheduled_at = $scheduled_at;

        return $this;
    }



    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->create_at;
    }

    public function setCreateAt(\DateTimeImmutable $create_at): self
    {
        $this->create_at = $create_at;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function updateTotalWeightAndArea(): void
    {
        $totalWeight = 0;
        $totalArea = 0;

        foreach ($this->packages as $package) {
            $totalWeight += $package->getWeight() * $package->getQuantity();
            $totalArea += $package->getLength() * $package->getWidth()/1000000 * $package->getQuantity();
        }

        $this->weight = $totalWeight;
        $this->area = $totalArea;
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

    public function getDistance(): ?int
    {
        return $this->distance;
    }

    public function setDistance(int $distance): self
    {
        $this->distance = $distance;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection<int, Package>
     */
    public function getPackages(): Collection
    {
        return $this->packages;
    }

    public function addPackage(Package $package): self
    {
        if (!$this->packages->contains($package)) {
            $this->packages->add($package);
            $package->setMission($this);
        }

        return $this;
    }

    public function removePackage(Package $package): self
    {
        if ($this->packages->removeElement($package)) {
            // set the owning side to null (unless already changed)
            if ($package->getMission() === $this) {
                $package->setMission(null);
            }
        }

        return $this;
    }

    public function getPickupPlace(): ?Place
    {
        return $this->pickupPlace;
    }

    public function setPickupPlace(?Place $pickupPlace): static
    {
        $this->pickupPlace = $pickupPlace;

        return $this;
    }

    public function getDeliveryPlace(): ?Place
    {
        return $this->deliveryPlace;
    }

    public function setDeliveryPlace(?Place $deliveryPlace): static
    {
        $this->deliveryPlace = $deliveryPlace;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isPicked(): ?bool
    {
        return $this->picked;
    }

    public function setPicked(?bool $picked): static
    {
        $this->picked = $picked;

        return $this;
    }

    public function isDelivered(): ?bool
    {
        return $this->delivered;
    }

    public function setDelivered(?bool $delivered): static
    {
        $this->delivered = $delivered;

        return $this;
    }

    public function getPickingVehicle(): ?Vehicle
    {
        return $this->pickingVehicle;
    }

    public function setPickingVehicle(?Vehicle $pickingVehicle): static
    {
        $this->pickingVehicle = $pickingVehicle;

        return $this;
    }

    public function getDeliveringVehicle(): ?Vehicle
    {
        return $this->deliveringVehicle;
    }

    public function setDeliveringVehicle(?Vehicle $deliveringVehicle): static
    {
        $this->deliveringVehicle = $deliveringVehicle;

        return $this;
    }

    /**
     * @return Collection<int, MediaUpload>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(MediaUpload $upload): static
    {
        if (!$this->documents->contains($upload)) {
            $this->documents->add($upload);
            $upload->setMission($this);
        }

        return $this;
    }

    public function removeDocument(MediaUpload $upload): static
    {
        if ($this->documents->removeElement($upload)) {
            // set the owning side to null (unless already changed)
            if ($upload->getMission() === $this) {
                $upload->setMission(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PictureUpload>
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(PictureUpload $picture): static
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures->add($picture);
            $picture->setMission($this);
        }

        return $this;
    }

    public function removePicture(PictureUpload $picture): static
    {
        if ($this->pictures->removeElement($picture)) {
            // set the owning side to null (unless already changed)
            if ($picture->getMission() === $this) {
                $picture->setMission(null);
            }
        }

        return $this;
    }

    public function isExpress(): ?bool
    {
        return $this->express;
    }

    public function setExpress(bool $express): static
    {
        $this->express = $express;

        return $this;
    }

    public function getArea(): ?string
    {
        return $this->area;
    }

    public function setArea(string $area): static
    {
        $this->area = $area;

        return $this;
    }

    public function getDdtReference(): ?string
    {
        return $this->ddt_reference;
    }

    public function setDdtReference(?string $ddt_reference): static
    {
        $this->ddt_reference = $ddt_reference;

        return $this;
    }

    public function getAvailableAt(): ?\DateTimeImmutable
    {
        return $this->available_at;
    }

    public function setAvailableAt(?\DateTimeImmutable $available_at): static
    {
        $this->available_at = $available_at;

        return $this;
    }

    public function getDeliveredBeforeAt(): ?\DateTimeImmutable
    {
        return $this->delivered_before_at;
    }

    public function setDeliveredBeforeAt(?\DateTimeImmutable $delivered_before_at): static
    {
        $this->delivered_before_at = $delivered_before_at;

        return $this;
    }

}

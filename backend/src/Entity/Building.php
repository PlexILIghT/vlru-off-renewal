<?php

namespace App\Entity;

use App\Repository\BuildingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: BuildingRepository::class)]
class Building
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'buildings')]
    private ?Street $street = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $number = null;

    #[ORM\ManyToOne(inversedBy: 'buildings')]
    private ?District $district = null;

    #[ORM\Column]
    private ?bool $isFake = null;

    #[ORM\ManyToOne(inversedBy: 'buildings')]
    private ?FolkDistrict $folkDistrict = null;

    #[ORM\ManyToOne(inversedBy: 'buildings')]
    private ?BigFolkDistrict $bigFolkDistrict = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'buildings')]
    private ?City $city = null;

    #[ORM\Column(nullable: true)]
    private ?array $coordinates = null;

    /**
     * @var Collection<int, Blackout>
     */
    #[ORM\ManyToMany(targetEntity: Blackout::class, mappedBy: 'buildings')]
    private Collection $blackouts;

    public function __construct()
    {
        $this->blackouts = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getStreet(): ?Street
    {
        return $this->street;
    }

    public function setStreet(?Street $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getDistrict(): ?District
    {
        return $this->district;
    }

    public function setDistrict(?District $district): static
    {
        $this->district = $district;

        return $this;
    }

    public function isFake(): ?bool
    {
        return $this->isFake;
    }

    public function setIsFake(bool $isFake): static
    {
        $this->isFake = $isFake;

        return $this;
    }

    public function getFolkDistrict(): ?FolkDistrict
    {
        return $this->folkDistrict;
    }

    public function setFolkDistrict(?FolkDistrict $folkDistrict): static
    {
        $this->folkDistrict = $folkDistrict;

        return $this;
    }

    public function getBigFolkDistrict(): ?BigFolkDistrict
    {
        return $this->bigFolkDistrict;
    }

    public function setBigFolkDistrict(?BigFolkDistrict $bigFolkDistrict): static
    {
        $this->bigFolkDistrict = $bigFolkDistrict;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCoordinates(): ?array
    {
        return $this->coordinates;
    }

    public function setCoordinates(?array $coordinates): static
    {
        $this->coordinates = $coordinates;

        return $this;
    }

    /**
     * @return Collection<int, Blackout>
     */
    public function getBlackouts(): Collection
    {
        return $this->blackouts;
    }

    public function addBlackout(Blackout $blackout): static
    {
        if (!$this->blackouts->contains($blackout)) {
            $this->blackouts->add($blackout);
            $blackout->addBuilding($this);
        }

        return $this;
    }

    public function removeBlackout(Blackout $blackout): static
    {
        if ($this->blackouts->removeElement($blackout)) {
            $blackout->removeBuilding($this);
        }

        return $this;
    }
}

<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\BuildingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: BuildingRepository::class)]
//#[ApiResource(
//    operations: [
//        new GetCollection(normalizationContext: ['groups' => ['building:list']]),
//        new Get(normalizationContext: ['groups' => ['building:detail']]),
//        new Post(denormalizationContext: ['groups' => ['building:write']]),
//        new Put(denormalizationContext: ['groups' => ['building:update']]),
//        new Patch(denormalizationContext: ['groups' => ['building:update']]),
//        new Delete(),
//    ]
//)]
class Building
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'uuid')]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'buildings')]
    #[Groups(['building:list', 'building:detail', 'building:write'])]
    private ?Street $street = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['building:list', 'building:detail', 'building:write', 'building:update'])]
    private ?string $number = null;

    #[ORM\ManyToOne(inversedBy: 'buildings')]
    #[Groups(['building:list', 'building:detail', 'building:write'])]
    private ?District $district = null;

    #[ORM\Column]
    #[Groups(['building:list', 'building:detail', 'building:write', 'building:update'])]
    private ?bool $isFake = null;

    #[ORM\ManyToOne(inversedBy: 'buildings')]
    #[Groups(['building:detail', 'building:write'])]
    private ?FolkDistrict $folkDistrict = null;

    #[ORM\ManyToOne(inversedBy: 'buildings')]
    #[Groups(['building:detail', 'building:write'])]
    private ?BigFolkDistrict $bigFolkDistrict = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['building:detail', 'building:write', 'building:update'])]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'buildings')]
    #[Groups(['building:list', 'building:detail', 'building:write'])]
    private ?City $city = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['building:detail', 'building:write', 'building:update'])]
    private ?array $coordinates = null;

    /**
     * @var Collection<int, Blackout>
     */
    #[ORM\ManyToMany(targetEntity: Blackout::class, mappedBy: 'buildings')]
    #[Groups(['building:detail'])]
    private Collection $blackouts;

    public function __construct()
    {
        $this->blackouts = new ArrayCollection();
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

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;

        return $this;
    }
}

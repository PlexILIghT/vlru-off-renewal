<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: CityRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['city:list']]),
        new Post(denormalizationContext: ['groups' => ['city:write']]),
        new Get(normalizationContext: ['groups' => ['city:detail']]),
        new Put(denormalizationContext: ['groups' => ['city:write']]),
        new Delete(),
    ]
)]
class City
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['city:list', 'city:detail', 'building:list', 'building:detail'])]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['city:list', 'city:detail'])]
    private ?string $name = null;

    /**
     * @var Collection<int, Street>
     */
    #[ORM\OneToMany(targetEntity: Street::class, mappedBy: 'city')]
    #[Groups(['city:detail'])]
    private Collection $streets;

    /**
     * @var Collection<int, Building>
     */
    #[ORM\OneToMany(targetEntity: Building::class, mappedBy: 'city')]
    private Collection $buildings;

    public function __construct()
    {
        $this->streets = new ArrayCollection();
        $this->buildings = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Street>
     */
    public function getStreets(): Collection
    {
        return $this->streets;
    }

    public function addStreet(Street $street): static
    {
        if (!$this->streets->contains($street)) {
            $this->streets->add($street);
            $street->setCity($this);
        }

        return $this;
    }

    public function removeStreet(Street $street): static
    {
        if ($this->streets->removeElement($street)) {
            // set the owning side to null (unless already changed)
            if ($street->getCity() === $this) {
                $street->setCity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Building>
     */
    public function getBuildings(): Collection
    {
        return $this->buildings;
    }

    public function addBuilding(Building $building): static
    {
        if (!$this->buildings->contains($building)) {
            $this->buildings->add($building);
            $building->setCity($this);
        }

        return $this;
    }

    public function removeBuilding(Building $building): static
    {
        if ($this->buildings->removeElement($building)) {
            // set the owning side to null (unless already changed)
            if ($building->getCity() === $this) {
                $building->setCity(null);
            }
        }

        return $this;
    }
}

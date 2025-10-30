<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\BlackoutRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: BlackoutRepository::class)]
//#[ApiResource(
//    operations: [
//        new GetCollection(normalizationContext: ['groups' => ['blackout:list']]),
//        new Get(
//            uriTemplate: '/blackouts/active',
//            name: 'blackouts_active'
//        ),
//        new Post(denormalizationContext: ['groups' => ['blackout:write']]),
//        new Delete(),
//    ]
//)]
class Blackout
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'uuid')]
    private ?Uuid $id = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['blackout:list', 'blackout:detail', 'blackout:write'])]
    private ?\DateTime $startDate = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['blackout:list', 'blackout:detail', 'blackout:write'])]
    private ?\DateTime $endDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['blackout:detail', 'blackout:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['blackout:detail', 'blackout:write'])]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['blackout:detail', 'blackout:write'])]
    private ?string $initiatorName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['blackout:detail', 'blackout:write'])]
    private ?string $source = null;

    /**
     * @var Collection<int, Building>
     */
    #[ORM\ManyToMany(targetEntity: Building::class, inversedBy: 'blackouts')]
    #[Groups(['blackout:list', 'blackout:detail', 'blackout:write'])]
    private Collection $buildings;

    public function __construct()
    {
        $this->buildings = new ArrayCollection();
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTime $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTime $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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

    public function getInitiatorName(): ?string
    {
        return $this->initiatorName;
    }

    public function setInitiatorName(?string $initiatorName): static
    {
        $this->initiatorName = $initiatorName;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): static
    {
        $this->source = $source;

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
        }

        return $this;
    }

    public function removeBuilding(Building $building): static
    {
        $this->buildings->removeElement($building);

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

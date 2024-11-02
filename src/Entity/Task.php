<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $Name = null;

    #[ORM\Column]
    private ?float $Cost = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $LimitDate = null;

    #[ORM\Column]
    private ?int $PresentationOrder = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): static
    {
        $this->Name = $Name;

        return $this;
    }

    public function getCost(): ?float
    {
        return $this->Cost;
    }

    public function setCost(float $Cost): static
    {
        $this->Cost = $Cost;

        return $this;
    }

    public function getLimitDate(): ?\DateTimeInterface
    {
        return $this->LimitDate;
    }

    public function setLimitDate(\DateTimeInterface $LimitDate): static
    {
        $this->LimitDate = $LimitDate;

        return $this;
    }

    public function getPresentationOrder(): ?int
    {
        return $this->PresentationOrder;
    }

    public function setPresentationOrder(int $PresentationOrder): static
    {
        $this->PresentationOrder = $PresentationOrder;

        return $this;
    }
}

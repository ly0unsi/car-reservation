<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]

class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    /**
     * @MaxDepth(1)
     *  @Groups({"reservation:read"})
     */
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    /**
     * @MaxDepth(1)
     * @Groups({"reservation:read"})
     */
    /**
     * @Assert\GreaterThanOrEqual(propertyPath="startDate", message="End date must be greater than or equal to start date.")
     */
    private ?\DateTimeInterface $endDate = null;

    #[ORM\ManyToOne(inversedBy: 'reservation')]
    /**
     * @MaxDepth(1)
     */
    private ?Car $car = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    /**
     * @MaxDepth(1)
     */
    private ?User $reserver = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCar(): ?Car
    {
        return $this->car;
    }

    public function setCar(?Car $car): static
    {
        $this->car = $car;

        return $this;
    }

    public function getReserver(): ?User
    {
        return $this->reserver;
    }

    public function setReserver(?User $reserver): static
    {
        $this->reserver = $reserver;

        return $this;
    }
}
<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]

/**
 * @OA\Schema(
 *     description="Reservation model",
 *     type="object",
 *     title="Reservation model"
 * )
 */
class Reservation
{
    /**
     * @OA\Property(
     *     format="int64",
     *     description="The unique identifier for the reservation",
     *     title="ID"
     * )
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null; // Identifiant unique de la réservation

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $debut = null; // Date de début de la réservation

    #[ORM\Column]
    private ?bool $valide = false; // Indique si la réservation est valide

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null; // Utilisateur client associé à la réservation

    #[ORM\ManyToOne]
    private ?User $professionel = null; // Utilisateur professionnel associé à la réservation

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDebut(): ?\DateTimeInterface
    {
        return $this->debut;
    }

    public function setDebut(\DateTimeInterface $debut): static
    {
        $this->debut = $debut;

        return $this;
    }

    public function isValide(): ?bool
    {
        return $this->valide;
    }

    public function setValide(bool $valide): static
    {
        $this->valide = $valide;

        return $this;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getProfessionel(): ?User
    {
        return $this->professionel;
    }

    public function setProfessionel(?User $professionel): static
    {
        $this->professionel = $professionel;

        return $this;
    }
}

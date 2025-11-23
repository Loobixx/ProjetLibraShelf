<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateReservation = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateRetourPrevue = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateRetourReelle = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Personne $personne = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Exemplaire $exemplaire = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ouvrage $ouvrage = null;

    public function __construct()
    {
        $this->dateReservation = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateReservation(): ?\DateTimeImmutable
    {
        return $this->dateReservation;
    }

    public function setDateReservation(\DateTimeImmutable $dateReservation): self
    {
        $this->dateReservation = $dateReservation;
        return $this;
    }

    public function getDateRetourPrevue(): ?\DateTimeImmutable
    {
        return $this->dateRetourPrevue;
    }

    public function setDateRetourPrevue(?\DateTimeImmutable $dateRetourPrevue): self
    {
        $this->dateRetourPrevue = $dateRetourPrevue;
        return $this;
    }

    public function getDateRetourReelle(): ?\DateTimeImmutable
    {
        return $this->dateRetourReelle;
    }

    public function setDateRetourReelle(?\DateTimeImmutable $dateRetourReelle): self
    {
        $this->dateRetourReelle = $dateRetourReelle;
        return $this;
    }

    public function getPersonne(): ?Personne
    {
        return $this->personne;
    }

    public function setPersonne(?Personne $personne): self
    {
        $this->personne = $personne;
        return $this;
    }

    public function getExemplaire(): ?Exemplaire
    {
        return $this->exemplaire;
    }

    public function setExemplaire(?Exemplaire $exemplaire): self
    {
        $this->exemplaire = $exemplaire;
        return $this;
    }

    public function getOuvrage(): ?Ouvrage
    {
        return $this->ouvrage;
    }

    public function setOuvrage(?Ouvrage $ouvrage): self
    {
        $this->ouvrage = $ouvrage;
        return $this;
    }


    public function isRendu(): bool
    {
        return $this->dateRetourReelle !== null;
    }

    public function isEnAttente(): bool
    {
        return $this->exemplaire === null;
    }

    public function getJoursRestants(): ?int
    {
        if ($this->isRendu() || !$this->dateRetourPrevue) {
            return null;
        }
        $now = new \DateTimeImmutable();
        $interval = $now->diff($this->dateRetourPrevue);
        return $interval->invert ? -1 * $interval->days : $interval->days;
    }
}

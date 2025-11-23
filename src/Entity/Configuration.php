<?php

namespace App\Entity;

use App\Repository\ConfigurationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConfigurationRepository::class)]
class Configuration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $maxLivres = null;

    #[ORM\Column]
    private ?int $dureeEmprunt = null;

    #[ORM\Column]
    private ?float $montantPenalite = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMaxLivres(): ?int
    {
        return $this->maxLivres;
    }

    public function setMaxLivres(int $maxLivres): static
    {
        $this->maxLivres = $maxLivres;

        return $this;
    }

    public function getDureeEmprunt(): ?int
    {
        return $this->dureeEmprunt;
    }

    public function setDureeEmprunt(int $dureeEmprunt): static
    {
        $this->dureeEmprunt = $dureeEmprunt;

        return $this;
    }

    public function getMontantPenalite(): ?float
    {
        return $this->montantPenalite;
    }

    public function setMontantPenalite(float $montantPenalite): static
    {
        $this->montantPenalite = $montantPenalite;

        return $this;
    }
}

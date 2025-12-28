<?php

namespace App\Entity;

use App\Repository\ExemplaireRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\EtatOuvrage;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ExemplaireRepository::class)]
#[UniqueEntity(fields: ['cote'], message: "Cette cote existe déjà !")]
class Exemplaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: "La cote est obligatoire.")]
    #[Assert\Length(min: 3, minMessage: "La cote doit faire au moins 3 caractères.")]
    private ?string $cote = null;

    #[ORM\Column(enumType: EtatOuvrage::class)]
    private ?EtatOuvrage $etat = null;

    #[ORM\Column]
    private ?bool $disponible = true;

    #[ORM\ManyToOne(inversedBy: 'exemplaires')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: "La cote est obligatoire.")]
    // RÈGLE : Un exemplaire ne peut pas exister sans livre parent
    #[Assert\NotNull(message: "Vous devez associer cet exemplaire à un ouvrage.")]
    private ?Ouvrage $ouvrage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCote(): ?string
    {
        return $this->cote;
    }

    public function setCote(string $cote): self
    {
        $this->cote = $cote;

        return $this;
    }

    public function getEtat(): ?EtatOuvrage
    {
        return $this->etat;
    }

    public function setEtat(EtatOuvrage $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function isDisponible(): ?bool
    {
        return $this->disponible;
    }

    public function setDisponible(bool $disponible): self
    {
        $this->disponible = $disponible;

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
}

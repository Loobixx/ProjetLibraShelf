<?php

namespace App\Enum;

enum EtatOuvrage: string
{
    case NEW = 'NEW';
    case GOOD = 'GOOD';
    case DAMAGED = 'DAMAGED';

    // Cette méthode permet d'afficher un joli nom en français partout
    public function getLabel(): string
    {
        return match ($this) {
            self::NEW => 'Neuf',
            self::GOOD => 'Bon état',
            self::DAMAGED => 'Abîmé',
        };
    }

    // Je te remets la méthode pour les couleurs (très utile pour Bootstrap)
    public function getBadgeColor(): string
    {
        return match ($this) {
            self::NEW => 'success',      // Vert
            self::GOOD => 'warning',        // Bleu
            self::DAMAGED => 'danger',  // Jaune/Orange
        };
    }
}

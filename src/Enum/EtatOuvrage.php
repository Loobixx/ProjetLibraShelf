<?php

namespace App\Enum;

enum EtatOuvrage: string
{
    case NEW = 'new';
    case GOOD = 'good';
    case DAMAGED = 'damaged';
    case LOST = 'lost';

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
            self::GOOD => 'info',        // Bleu
            self::DAMAGED => 'warning',  // Jaune/Orange
            self::LOST => 'danger',      // Rouge
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Grille de mise en avant d'un événement. Le tarif est recopié dans
 * l'événement au moment de l'achat : une grille qui évolue ne réécrit pas
 * ce qui a déjà été facturé.
 */
enum FormuleSponsoring: string
{
    case Boost24 = 'boost24';
    case Top7 = 'top7';
    case Premium30 = 'premium30';

    public function libelle(): string
    {
        return match ($this) {
            self::Boost24 => 'Coup de projecteur',
            self::Top7 => 'Top du fil',
            self::Premium30 => 'Premium',
        };
    }

    public function resume(): string
    {
        return match ($this) {
            self::Boost24 => '24 h en tête du fil',
            self::Top7 => '7 jours en tête du fil, badge « Sponsorisé »',
            self::Premium30 => '30 jours en tête du fil, priorité maximale',
        };
    }

    public function tarif(): float
    {
        return match ($this) {
            self::Boost24 => 19.0,
            self::Top7 => 49.0,
            self::Premium30 => 129.0,
        };
    }

    public function dureeEnHeures(): int
    {
        return match ($this) {
            self::Boost24 => 24,
            self::Top7 => 24 * 7,
            self::Premium30 => 24 * 30,
        };
    }
}

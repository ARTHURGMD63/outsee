<?php

declare(strict_types=1);

namespace App\Enum;

/** Offre souscrite par un partenaire. */
enum OffreClient: string
{
    case Aucune = 'aucune';
    case Fondateur = 'fondateur';
    case Essentiel = 'essentiel';
    case Premium = 'premium';
    case Bde = 'bde';

    public function libelle(): string
    {
        return match ($this) {
            self::Aucune => 'Aucune',
            self::Fondateur => 'Fondateur',
            self::Essentiel => 'Essentiel',
            self::Premium => 'Premium',
            self::Bde => 'BDE',
        };
    }
}

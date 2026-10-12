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
}

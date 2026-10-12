<?php

declare(strict_types=1);

namespace App\Enum;

/** Traitement d'un signalement par la modération. */
enum StatutSignalement: string
{
    case Nouveau = 'nouveau';
    case Traite = 'traite';
}

<?php

declare(strict_types=1);

namespace App\Enum;

/** Cycle de vie d'une réservation : inscrit, puis présent après le scan, ou annulé. */
enum StatutInscription: string
{
    case Inscrit = 'inscrit';
    case Checkin = 'checkin';
    case Annule = 'annule';
}

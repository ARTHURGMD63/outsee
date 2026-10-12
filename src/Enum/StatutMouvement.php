<?php

declare(strict_types=1);

namespace App\Enum;

/** « Prévu » : facturé ou engagé, pas encore en banque. */
enum StatutMouvement: string
{
    case Prevu = 'prevu';
    case Regle = 'regle';
}

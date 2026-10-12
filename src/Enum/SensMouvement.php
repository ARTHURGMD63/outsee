<?php

declare(strict_types=1);

namespace App\Enum;

/** Sens d'un mouvement financier. */
enum SensMouvement: string
{
    case Recette = 'recette';
    case Depense = 'depense';
}

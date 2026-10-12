<?php

declare(strict_types=1);

namespace App\Enum;

/** Catégorie d'un établissement et de ses événements. */
enum TypeLieu: string
{
    case Bar = 'bar';
    case Boite = 'boite';
    case Resto = 'resto';
    case Afterwork = 'afterwork';
}

<?php

declare(strict_types=1);

namespace App\Enum;

/** Catégorie d'un client du CRM. */
enum CategorieClient: string
{
    case Bar = 'bar';
    case Boite = 'boite';
    case Resto = 'resto';
    case Afterwork = 'afterwork';
    case Bde = 'bde';
    case Autre = 'autre';
}

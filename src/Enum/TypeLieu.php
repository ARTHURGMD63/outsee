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

    public function libelle(): string
    {
        return match ($this) {
            self::Bar => 'Bar',
            self::Boite => 'Boîte de nuit',
            self::Resto => 'Restaurant',
            self::Afterwork => 'Afterwork',
        };
    }
}

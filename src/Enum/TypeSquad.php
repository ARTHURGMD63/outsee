<?php

declare(strict_types=1);

namespace App\Enum;

/** Activité d'une squad. */
enum TypeSquad: string
{
    case Running = 'running';
    case Velo = 'velo';
    case Muscu = 'muscu';
    case Culture = 'culture';
    case Autre = 'autre';

    public function libelle(): string
    {
        return match ($this) {
            self::Running => 'Running',
            self::Velo => 'Vélo',
            self::Muscu => 'Muscu',
            self::Culture => 'Culture',
            self::Autre => 'Autre',
        };
    }
}

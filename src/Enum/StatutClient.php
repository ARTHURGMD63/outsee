<?php

declare(strict_types=1);

namespace App\Enum;

/** Étapes du pipeline commercial. « Pause » et « perdu » sont des sorties. */
enum StatutClient: string
{
    case Prospect = 'prospect';
    case Contacte = 'contacte';
    case Rdv = 'rdv';
    case Essai = 'essai';
    case Actif = 'actif';
    case Pause = 'pause';
    case Perdu = 'perdu';

    public function libelle(): string
    {
        return match ($this) {
            self::Prospect => 'Prospect',
            self::Contacte => 'Contacté',
            self::Rdv => 'Rendez-vous',
            self::Essai => 'Essai',
            self::Actif => 'Actif',
            self::Pause => 'En pause',
            self::Perdu => 'Perdu',
        };
    }
}

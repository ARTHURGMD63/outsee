<?php

declare(strict_types=1);

namespace App\Enum;

/** Niveau attendu dans une squad. */
enum NiveauSquad: string
{
    case Tous = 'tous';
    case Debutant = 'debutant';
    case Intermediaire = 'inter';
    case Avance = 'avance';

    public function libelle(): string
    {
        return match ($this) {
            self::Tous => 'Tous niveaux',
            self::Debutant => 'Débutant',
            self::Intermediaire => 'Intermédiaire',
            self::Avance => 'Avancé',
        };
    }
}

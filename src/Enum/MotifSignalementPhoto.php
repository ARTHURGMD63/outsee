<?php

declare(strict_types=1);

namespace App\Enum;

/** Motif du signalement d'une photo de soirée. */
enum MotifSignalementPhoto: string
{
    case NApparaitPas = 'n_apparait_pas';
    case ContenuInapproprie = 'contenu_inapproprie';
    case Spam = 'spam';
    case Autre = 'autre';
}

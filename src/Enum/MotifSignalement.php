<?php

declare(strict_types=1);

namespace App\Enum;

/** Motif du signalement d'un compte. */
enum MotifSignalement: string
{
    case Spam = 'spam';
    case Harcelement = 'harcelement';
    case ContenuInapproprie = 'contenu_inapproprie';
    case Usurpation = 'usurpation';
    case Autre = 'autre';
}

<?php

declare(strict_types=1);

namespace App\Enum;

/** Thème d'affichage choisi par l'utilisateur. */
enum Theme: string
{
    case Clair = 'light';
    case Sombre = 'dark';
}

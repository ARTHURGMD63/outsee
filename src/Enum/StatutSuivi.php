<?php

declare(strict_types=1);

namespace App\Enum;

/** Un abonnement à un compte attend l'accord de la personne suivie. */
enum StatutSuivi: string
{
    case EnAttente = 'pending';
    case Accepte = 'accepted';
}

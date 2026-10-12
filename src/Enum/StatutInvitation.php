<?php

declare(strict_types=1);

namespace App\Enum;

/** Réponse à une invitation. */
enum StatutInvitation: string
{
    case EnAttente = 'pending';
    case Acceptee = 'accepted';
    case Refusee = 'declined';
}

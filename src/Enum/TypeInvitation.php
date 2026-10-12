<?php

declare(strict_types=1);

namespace App\Enum;

/** Ce à quoi on invite : un événement ou une squad. */
enum TypeInvitation: string
{
    case Evenement = 'event';
    case Squad = 'squad';
}

<?php

declare(strict_types=1);

namespace App\Enum;

/** Nature d'un échange avec un client du CRM. */
enum TypeInteraction: string
{
    case Appel = 'appel';
    case Visite = 'visite';
    case Email = 'email';
    case Demo = 'demo';
    case Relance = 'relance';
    case Note = 'note';
}

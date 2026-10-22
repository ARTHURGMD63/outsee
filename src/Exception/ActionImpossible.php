<?php

declare(strict_types=1);

namespace App\Exception;

/** Une règle métier refuse l'action ; le message est destiné à l'utilisateur. */
final class ActionImpossible extends \DomainException
{
}

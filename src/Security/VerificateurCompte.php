<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Utilisateur;
use App\Enum\TypeCompte;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/** Un compte supprimé (anonymisé) ne peut plus se connecter. */
final class VerificateurCompte implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof Utilisateur && TypeCompte::Supprime === $user->getType()) {
            throw new CustomUserMessageAccountStatusException('Ce compte a été supprimé.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}

<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Evenement;
use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Seul le propriétaire de l'établissement modifie ou contrôle ses événements.
 *
 * @extends Voter<string, Evenement>
 */
final class EvenementVoter extends Voter
{
    public const GERER = 'EVENEMENT_GERER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::GERER === $attribute && $subject instanceof Evenement;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $utilisateur = $token->getUser();

        return $utilisateur instanceof Utilisateur
            && $subject->getEtablissement()->getProprietaire() === $utilisateur;
    }
}

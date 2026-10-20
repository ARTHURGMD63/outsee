<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Utilisateur;
use App\Service\Journal;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/** Chaque connexion réussie laisse une trace dans le journal d'activité. */
#[AsEventListener]
final readonly class JournalConnexionListener
{
    public function __construct(private Journal $journal)
    {
    }

    public function __invoke(LoginSuccessEvent $evenement): void
    {
        $utilisateur = $evenement->getUser();

        if ($utilisateur instanceof Utilisateur) {
            $this->journal->enregistrer(Journal::CONNEXION, $utilisateur);
        }
    }
}

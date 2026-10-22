<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Evenement;
use App\Entity\Inscription;
use App\Entity\Utilisateur;
use App\Exception\ActionImpossible;
use App\Repository\EvenementRepository;
use App\Repository\InscriptionRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

/** Réserver et annuler une place à un événement. */
final readonly class Reservation
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EvenementRepository $evenements,
        private InscriptionRepository $inscriptions,
        private Gamification $gamification,
        private Journal $journal,
    ) {
    }

    /**
     * La ligne de l'événement est verrouillée le temps de compter les places :
     * deux réservations simultanées pour la dernière place ne peuvent pas
     * passer toutes les deux.
     */
    public function reserver(Utilisateur $etudiant, Evenement $evenement): Inscription
    {
        if (!$etudiant->estEtudiant()) {
            throw new ActionImpossible('Seuls les comptes étudiants peuvent réserver.');
        }

        $inscription = $this->entityManager->wrapInTransaction(function () use ($etudiant, $evenement): Inscription {
            $this->entityManager->lock($evenement, LockMode::PESSIMISTIC_WRITE);

            if ($evenement->estPasse()) {
                throw new ActionImpossible('Cet événement est terminé.');
            }

            $existante = $this->inscriptions->findOneBy(['utilisateur' => $etudiant, 'evenement' => $evenement]);
            if (null !== $existante && $existante->estActive()) {
                throw new ActionImpossible('Tu es déjà inscrit à cet événement.');
            }

            if ($this->evenements->placesPrises($evenement) >= $evenement->getQuota()) {
                throw new ActionImpossible('Cet événement est complet.');
            }

            if (null !== $existante) {
                $existante->reactiver();

                return $existante;
            }

            $inscription = new Inscription($etudiant, $evenement);
            $this->entityManager->persist($inscription);

            return $inscription;
        });

        $this->gamification->attribuerBadges($etudiant, $evenement);
        $this->journal->enregistrer(Journal::RESERVATION, $etudiant, ['evenement' => $evenement->getId()]);

        return $inscription;
    }

    public function annuler(Inscription $inscription): void
    {
        if (!$inscription->estActive() || null !== $inscription->getCheckinLe()) {
            throw new ActionImpossible('Cette réservation ne peut plus être annulée.');
        }

        $inscription->annuler();
        $this->entityManager->flush();

        $this->journal->enregistrer(Journal::ANNULATION, $inscription->getUtilisateur(), [
            'evenement' => $inscription->getEvenement()->getId(),
        ]);
    }
}

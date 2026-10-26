<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Economie;
use App\Entity\Etablissement;
use App\Entity\Inscription;
use App\Enum\StatutInscription;
use App\Exception\ActionImpossible;
use App\Repository\InscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Le partenaire scanne le QR code d'un étudiant à l'entrée.
 *
 * L'économie est enregistrée à ce moment-là, et non à la réservation :
 * un étudiant qui ne vient pas n'a rien économisé.
 */
final readonly class ControleEntree
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private InscriptionRepository $inscriptions,
        private Gamification $gamification,
        private Journal $journal,
    ) {
    }

    public function valider(string $qrCode, Etablissement $etablissement): Inscription
    {
        $inscription = $this->inscriptions->findOneBy(['qrCode' => $this->nettoyer($qrCode)]);

        if (null === $inscription || $inscription->getEvenement()->getEtablissement() !== $etablissement) {
            throw new ActionImpossible('Ce pass ne correspond à aucune réservation chez toi.');
        }

        match ($inscription->getStatut()) {
            StatutInscription::Annule => throw new ActionImpossible('Cette réservation a été annulée.'),
            StatutInscription::Checkin => throw new ActionImpossible(\sprintf('Ce pass a déjà été scanné à %s.', $inscription->getCheckinLe()?->format('H\hi') ?? '?')),
            StatutInscription::Inscrit => null,
        };

        $inscription->validerEntree();

        $evenement = $inscription->getEvenement();
        if ($evenement->getEconomie() > 0) {
            $this->entityManager->persist(new Economie($inscription->getUtilisateur(), $evenement, $evenement->getEconomie()));
        }

        $this->entityManager->flush();
        $this->gamification->attribuerBadges($inscription->getUtilisateur());
        $this->journal->enregistrer(Journal::ENTREE_VALIDEE, $inscription->getUtilisateur(), ['evenement' => $evenement->getId()]);

        return $inscription;
    }

    /** Le QR peut contenir « outsee:<jeton> » ou le jeton seul. */
    private function nettoyer(string $qrCode): string
    {
        return strtolower(trim(preg_replace('/^(outsee|linkee|studentlink):/i', '', trim($qrCode)) ?? ''));
    }
}

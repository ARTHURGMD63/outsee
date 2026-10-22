<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Evenement;
use App\Entity\Utilisateur;
use App\Enum\StatutInscription;
use App\Enum\TypeLieu;
use App\Exception\ActionImpossible;
use App\Repository\EvenementRepository;
use App\Repository\InscriptionRepository;
use App\Service\Gamification;
use App\Service\Journal;
use App\Service\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/evenements')]
final class EvenementController extends AbstractController
{
    #[Route('', name: 'evenement_liste', methods: ['GET'])]
    public function liste(
        EvenementRepository $evenements,
        #[MapQueryParameter] ?string $type = null,
        #[MapQueryParameter] ?string $q = null,
    ): Response {
        $filtre = TypeLieu::tryFrom((string) $type);

        return $this->render('evenement/liste.html.twig', [
            'evenements' => $evenements->aVenir($filtre, $q),
            'filtre' => $filtre,
            'recherche' => $q,
            'types' => TypeLieu::cases(),
        ]);
    }

    #[Route('/{id<\d+>}', name: 'evenement_detail', methods: ['GET'])]
    public function detail(
        Evenement $evenement,
        EvenementRepository $evenements,
        InscriptionRepository $inscriptions,
        EntityManagerInterface $entityManager,
        #[CurrentUser] ?Utilisateur $utilisateur,
    ): Response {
        $inscription = null === $utilisateur ? null : $inscriptions->findOneBy(['utilisateur' => $utilisateur, 'evenement' => $evenement]);
        $dejaNote = null !== $utilisateur
            && null !== $entityManager->getRepository(Avis::class)->findOneBy(['auteur' => $utilisateur, 'evenement' => $evenement]);

        return $this->render('evenement/detail.html.twig', [
            'evenement' => $evenement,
            'places_prises' => $evenements->placesPrises($evenement),
            'inscription' => $inscription?->estActive() ? $inscription : null,
            'peut_noter' => StatutInscription::Checkin === $inscription?->getStatut() && !$dejaNote,
        ]);
    }

    #[Route('/{id<\d+>}/reserver', name: 'evenement_reserver', methods: ['POST'])]
    #[IsGranted('ROLE_ETUDIANT')]
    #[IsCsrfTokenValid('reserver')]
    public function reserver(Evenement $evenement, Reservation $reservation, #[CurrentUser] Utilisateur $utilisateur): Response
    {
        try {
            $reservation->reserver($utilisateur, $evenement);
            $this->addFlash('succes', 'C\'est réservé ! Ton pass est prêt.');

            return $this->redirectToRoute('pass');
        } catch (ActionImpossible $refus) {
            $this->addFlash('erreur', $refus->getMessage());

            return $this->redirectToRoute('evenement_detail', ['id' => $evenement->getId()]);
        }
    }

    #[Route('/{id<\d+>}/annuler', name: 'evenement_annuler', methods: ['POST'])]
    #[IsGranted('ROLE_ETUDIANT')]
    #[IsCsrfTokenValid('annuler')]
    public function annuler(Evenement $evenement, InscriptionRepository $inscriptions, Reservation $reservation, #[CurrentUser] Utilisateur $utilisateur): Response
    {
        $inscription = $inscriptions->findOneBy(['utilisateur' => $utilisateur, 'evenement' => $evenement]);

        try {
            if (null === $inscription) {
                throw new ActionImpossible('Tu n\'as pas de réservation pour cet événement.');
            }
            $reservation->annuler($inscription);
            $this->addFlash('succes', 'Ta réservation est annulée.');
        } catch (ActionImpossible $refus) {
            $this->addFlash('erreur', $refus->getMessage());
        }

        return $this->redirectToRoute('evenement_detail', ['id' => $evenement->getId()]);
    }

    /** Un avis n'est possible qu'après avoir été scanné à l'entrée, et une seule fois. */
    #[Route('/{id<\d+>}/avis', name: 'evenement_avis', methods: ['POST'])]
    #[IsGranted('ROLE_ETUDIANT')]
    #[IsCsrfTokenValid('avis')]
    public function avis(
        Evenement $evenement,
        Request $requete,
        InscriptionRepository $inscriptions,
        EntityManagerInterface $entityManager,
        Gamification $gamification,
        Journal $journal,
        #[CurrentUser] Utilisateur $utilisateur,
    ): Response {
        $inscription = $inscriptions->findOneBy(['utilisateur' => $utilisateur, 'evenement' => $evenement]);
        $note = $requete->request->getInt('note');
        $dejaNote = null !== $entityManager->getRepository(Avis::class)->findOneBy(['auteur' => $utilisateur, 'evenement' => $evenement]);

        if (StatutInscription::Checkin !== $inscription?->getStatut() || $dejaNote || $note < 1 || $note > 5) {
            $this->addFlash('erreur', 'Tu ne peux pas noter cet événement.');

            return $this->redirectToRoute('evenement_detail', ['id' => $evenement->getId()]);
        }

        $commentaire = trim((string) $requete->request->get('commentaire'));
        $entityManager->persist(new Avis($utilisateur, $evenement, $note, '' === $commentaire ? null : mb_substr($commentaire, 0, 1000)));
        $entityManager->flush();

        $gamification->attribuerBadges($utilisateur);
        $journal->enregistrer(Journal::AVIS_DEPOSE, $utilisateur, ['evenement' => $evenement->getId(), 'note' => $note]);
        $this->addFlash('succes', 'Merci pour ton avis !');

        return $this->redirectToRoute('evenement_detail', ['id' => $evenement->getId()]);
    }
}

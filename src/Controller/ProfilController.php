<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\BadgeObtenu;
use App\Entity\Utilisateur;
use App\Form\ProfilType;
use App\Repository\EntreeJournalRepository;
use App\Repository\InscriptionRepository;
use App\Service\Gamification;
use App\Service\Journal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/moi')]
#[IsGranted('ROLE_USER')]
final class ProfilController extends AbstractController
{
    #[Route('', name: 'profil', methods: ['GET'])]
    public function profil(
        Gamification $gamification,
        InscriptionRepository $inscriptions,
        EntreeJournalRepository $journal,
        EntityManagerInterface $entityManager,
        #[CurrentUser] Utilisateur $utilisateur,
    ): Response {
        $stats = $gamification->statistiques($utilisateur);
        $xp = Gamification::xp($stats);
        $niveau = Gamification::niveau($xp);

        return $this->render('profil/profil.html.twig', [
            'stats' => $stats,
            'xp' => $xp,
            'niveau' => $niveau,
            'xp_niveau_actuel' => Gamification::xpPourNiveau($niveau),
            'xp_niveau_suivant' => Gamification::xpPourNiveau($niveau + 1),
            'badges' => $entityManager->getRepository(BadgeObtenu::class)->findBy(['utilisateur' => $utilisateur], ['unlockedAt' => 'DESC']),
            'historique' => $inscriptions->historique($utilisateur, 5),
            'activite' => $journal->dernieresPour((int) $utilisateur->getId(), 8),
        ]);
    }

    #[Route('/modifier', name: 'profil_modifier', methods: ['GET', 'POST'])]
    public function modifier(Request $requete, EntityManagerInterface $entityManager, #[CurrentUser] Utilisateur $utilisateur): Response
    {
        $formulaire = $this->createForm(ProfilType::class, $utilisateur, ['etudiant' => $utilisateur->estEtudiant()])
            ->handleRequest($requete);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $entityManager->flush();
            $this->addFlash('succes', 'Ton profil est à jour.');

            return $this->redirectToRoute('profil');
        }

        if ($formulaire->isSubmitted()) {
            // Ne pas garder en mémoire les valeurs refusées : la barre de navigation lit l'utilisateur courant.
            $entityManager->refresh($utilisateur);
        }

        return $this->render('profil/modifier.html.twig', ['formulaire' => $formulaire]);
    }

    /**
     * Suppression du compte (RGPD) : l'identité est effacée, la ligne reste
     * pour que les statistiques des partenaires ne changent pas après coup.
     */
    #[Route('/supprimer', name: 'profil_supprimer', methods: ['POST'])]
    #[IsCsrfTokenValid('supprimer-compte')]
    public function supprimer(EntityManagerInterface $entityManager, Security $securite, Journal $journal, #[CurrentUser] Utilisateur $utilisateur): Response
    {
        $journal->enregistrer(Journal::SUPPRESSION_COMPTE, $utilisateur);
        $utilisateur->anonymiser();
        $entityManager->flush();

        $securite->logout(false);
        $this->addFlash('succes', 'Ton compte est supprimé. À bientôt peut-être.');

        return $this->redirectToRoute('accueil');
    }
}

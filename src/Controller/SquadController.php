<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Squad;
use App\Entity\Utilisateur;
use App\Enum\TypeSquad;
use App\Form\SquadType;
use App\Repository\SquadRepository;
use App\Service\Gamification;
use App\Service\Journal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/squads')]
final class SquadController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Gamification $gamification,
        private readonly Journal $journal,
    ) {
    }

    #[Route('', name: 'squad_liste', methods: ['GET'])]
    public function liste(
        SquadRepository $squads,
        #[MapQueryParameter] ?string $type = null,
        #[MapQueryParameter] bool $associations = false,
    ): Response {
        $filtre = TypeSquad::tryFrom((string) $type);

        return $this->render('squad/liste.html.twig', [
            'squads' => $squads->aVenir($filtre, $associations),
            'filtre' => $filtre,
            'associations' => $associations,
            'types' => TypeSquad::cases(),
        ]);
    }

    #[Route('/nouvelle', name: 'squad_nouvelle', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ETUDIANT')]
    public function nouvelle(Request $requete, #[CurrentUser] Utilisateur $utilisateur): Response
    {
        $squad = new Squad($utilisateur);
        $formulaire = $this->createForm(SquadType::class, $squad)->handleRequest($requete);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $this->entityManager->persist($squad);
            $this->entityManager->flush();
            $this->gamification->attribuerBadges($utilisateur);

            $this->addFlash('succes', 'Ta squad est publiée.');

            return $this->redirectToRoute('squad_detail', ['id' => $squad->getId()]);
        }

        return $this->render('squad/nouvelle.html.twig', ['formulaire' => $formulaire]);
    }

    #[Route('/{id<\d+>}', name: 'squad_detail', methods: ['GET'])]
    public function detail(Squad $squad): Response
    {
        return $this->render('squad/detail.html.twig', ['squad' => $squad]);
    }

    #[Route('/{id<\d+>}/rejoindre', name: 'squad_rejoindre', methods: ['POST'])]
    #[IsGranted('ROLE_ETUDIANT')]
    #[IsCsrfTokenValid('squad')]
    public function rejoindre(Squad $squad, #[CurrentUser] Utilisateur $utilisateur): Response
    {
        try {
            $squad->rejoindre($utilisateur);
            $this->entityManager->flush();
            $this->gamification->attribuerBadges($utilisateur);
            $this->journal->enregistrer(Journal::SQUAD_REJOINTE, $utilisateur, ['squad' => $squad->getId()]);
            $this->addFlash('succes', 'Tu fais partie de la squad !');
        } catch (\DomainException $refus) {
            $this->addFlash('erreur', $refus->getMessage());
        }

        return $this->redirectToRoute('squad_detail', ['id' => $squad->getId()]);
    }

    #[Route('/{id<\d+>}/quitter', name: 'squad_quitter', methods: ['POST'])]
    #[IsGranted('ROLE_ETUDIANT')]
    #[IsCsrfTokenValid('squad')]
    public function quitter(Squad $squad, #[CurrentUser] Utilisateur $utilisateur): Response
    {
        try {
            $squad->quitter($utilisateur);
            $this->entityManager->flush();
            $this->addFlash('succes', 'Tu as quitté la squad.');
        } catch (\DomainException $refus) {
            $this->addFlash('erreur', $refus->getMessage());
        }

        return $this->redirectToRoute('squad_detail', ['id' => $squad->getId()]);
    }
}

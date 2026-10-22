<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\EvenementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AccueilController extends AbstractController
{
    /** Un visiteur voit la présentation ; un membre arrive directement dans son espace. */
    #[Route('/', name: 'accueil', methods: ['GET'])]
    public function accueil(EvenementRepository $evenements): Response
    {
        $utilisateur = $this->getUser();

        if ($utilisateur instanceof Utilisateur) {
            return $this->redirectToRoute(match (true) {
                $this->isGranted('ROLE_ADMIN') => 'admin_tableau_de_bord',
                $utilisateur->estPartenaire() => 'partenaire_tableau_de_bord',
                default => 'evenement_liste',
            });
        }

        return $this->render('accueil/accueil.html.twig', [
            'evenements' => $evenements->aVenir(limite: 3),
        ]);
    }
}

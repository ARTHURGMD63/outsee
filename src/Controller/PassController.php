<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\InscriptionRepository;
use App\Service\Gamification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/** Le portefeuille de l'étudiant : ses pass à présenter à l'entrée. */
final class PassController extends AbstractController
{
    #[Route('/pass', name: 'pass', methods: ['GET'])]
    #[IsGranted('ROLE_ETUDIANT')]
    public function pass(InscriptionRepository $inscriptions, Gamification $gamification, #[CurrentUser] Utilisateur $utilisateur): Response
    {
        return $this->render('pass/pass.html.twig', [
            'pass' => $inscriptions->passActifs($utilisateur),
            'historique' => $inscriptions->historique($utilisateur, 5),
            'stats' => $gamification->statistiques($utilisateur),
        ]);
    }
}

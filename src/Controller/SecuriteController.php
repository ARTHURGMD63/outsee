<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecuriteController extends AbstractController
{
    #[Route('/connexion', name: 'connexion', methods: ['GET', 'POST'])]
    public function connexion(AuthenticationUtils $authentification): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('accueil');
        }

        return $this->render('securite/connexion.html.twig', [
            'dernier_email' => $authentification->getLastUsername(),
            'erreur' => $authentification->getLastAuthenticationError(),
        ]);
    }

    /** Interceptée par le pare-feu : cette méthode n'est jamais exécutée. */
    #[Route('/deconnexion', name: 'deconnexion', methods: ['GET'])]
    public function deconnexion(): never
    {
        throw new \LogicException('La déconnexion est gérée par le pare-feu.');
    }
}

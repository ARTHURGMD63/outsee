<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\InscriptionType;
use App\Form\Model\DemandeInscription;
use App\Service\CreationCompte;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InscriptionController extends AbstractController
{
    #[Route('/inscription', name: 'inscription', methods: ['GET', 'POST'])]
    public function inscription(Request $requete, CreationCompte $creationCompte, Security $securite): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('accueil');
        }

        $demande = new DemandeInscription();
        $formulaire = $this->createForm(InscriptionType::class, $demande);
        $formulaire->handleRequest($requete);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $utilisateur = $creationCompte->creer($demande);
            $securite->login($utilisateur, 'form_login', 'main');

            $this->addFlash('succes', \sprintf('Bienvenue sur Outsee, %s !', $utilisateur->getPrenom()));

            return $this->redirectToRoute($utilisateur->estPartenaire() ? 'partenaire_tableau_de_bord' : 'evenement_liste');
        }

        return $this->render('inscription/inscription.html.twig', [
            'formulaire' => $formulaire,
        ], new Response(status: $formulaire->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }
}

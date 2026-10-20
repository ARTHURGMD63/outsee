<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ReinitialisationMotDePasse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/mot-de-passe')]
final class MotDePasseController extends AbstractController
{
    #[Route('/oublie', name: 'mot_de_passe_oublie', methods: ['GET', 'POST'])]
    public function oublie(
        Request $requete,
        ReinitialisationMotDePasse $reinitialisation,
        #[Target('reinitialisation.limiter')] RateLimiterFactoryInterface $limiteur,
    ): Response {
        $formulaire = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'label' => 'Ton e-mail',
                'constraints' => [new Assert\NotBlank(), new Assert\Email()],
            ])
            ->getForm()
            ->handleRequest($requete);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            if (!$limiteur->create($requete->getClientIp())->consume()->isAccepted()) {
                $this->addFlash('erreur', 'Trop de demandes. Réessaie dans une heure.');

                return $this->redirectToRoute('mot_de_passe_oublie');
            }

            $reinitialisation->demander((string) $formulaire->get('email')->getData());
            $this->addFlash('succes', 'Si un compte existe avec cette adresse, un lien vient de t\'être envoyé. Il est valable une heure.');

            return $this->redirectToRoute('connexion');
        }

        return $this->render('mot_de_passe/oublie.html.twig', ['formulaire' => $formulaire]);
    }

    #[Route('/nouveau/{jeton}', name: 'mot_de_passe_nouveau', requirements: ['jeton' => '[a-f0-9]{64}'], methods: ['GET', 'POST'])]
    public function nouveau(string $jeton, Request $requete, ReinitialisationMotDePasse $reinitialisation): Response
    {
        $demande = $reinitialisation->demandeValide($jeton);
        if (null === $demande) {
            $this->addFlash('erreur', 'Ce lien n\'est plus valable. Fais une nouvelle demande.');

            return $this->redirectToRoute('mot_de_passe_oublie');
        }

        $formulaire = $this->createFormBuilder()
            ->add('motDePasse', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Nouveau mot de passe', 'attr' => ['autocomplete' => 'new-password']],
                'second_options' => ['label' => 'Confirmation', 'attr' => ['autocomplete' => 'new-password']],
                'invalid_message' => 'Les deux mots de passe ne correspondent pas.',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(min: 8, max: 4096)],
            ])
            ->getForm()
            ->handleRequest($requete);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $reinitialisation->changer($demande, (string) $formulaire->get('motDePasse')->getData());
            $this->addFlash('succes', 'Ton mot de passe est changé. Tu peux te connecter.');

            return $this->redirectToRoute('connexion');
        }

        return $this->render('mot_de_passe/nouveau.html.twig', ['formulaire' => $formulaire]);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ClientCrm;
use App\Entity\Etablissement;
use App\Entity\Evenement;
use App\Entity\Utilisateur;
use App\Enum\FormuleSponsoring;
use App\Exception\ActionImpossible;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use App\Repository\InscriptionRepository;
use App\Security\Voter\EvenementVoter;
use App\Service\ControleEntree;
use App\Service\Journal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/partenaire')]
#[IsGranted('ROLE_PARTENAIRE')]
final class PartenaireController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('', name: 'partenaire_tableau_de_bord', methods: ['GET'])]
    public function tableauDeBord(EvenementRepository $evenements, #[CurrentUser] Utilisateur $partenaire): Response
    {
        $etablissement = $this->etablissement($partenaire);
        $client = $this->entityManager->getRepository(ClientCrm::class)->findOneBy(['etablissement' => $etablissement]);
        $aVenir = $evenements->aVenirPour($etablissement);

        return $this->render('partenaire/tableau_de_bord.html.twig', [
            'etablissement' => $etablissement,
            'client' => $client,
            'evenements' => $aVenir,
            'places' => array_combine(
                array_map(static fn (Evenement $e): int => (int) $e->getId(), $aVenir),
                array_map($evenements->placesPrises(...), $aVenir),
            ),
        ]);
    }

    #[Route('/evenements/nouveau', name: 'partenaire_evenement_nouveau', methods: ['GET', 'POST'])]
    public function nouvelEvenement(Request $requete, Journal $journal, #[CurrentUser] Utilisateur $partenaire): Response
    {
        $evenement = new Evenement($this->etablissement($partenaire));
        $formulaire = $this->createForm(EvenementType::class, $evenement)->handleRequest($requete);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $this->entityManager->persist($evenement);
            $this->entityManager->flush();
            $journal->enregistrer(Journal::EVENEMENT_PUBLIE, $partenaire, ['evenement' => $evenement->getId()]);

            $this->addFlash('succes', 'Ton événement est en ligne.');

            return $this->redirectToRoute('partenaire_tableau_de_bord');
        }

        return $this->render('partenaire/evenement_formulaire.html.twig', ['formulaire' => $formulaire, 'evenement' => null]);
    }

    #[Route('/evenements/{id<\d+>}/modifier', name: 'partenaire_evenement_modifier', methods: ['GET', 'POST'])]
    #[IsGranted(EvenementVoter::GERER, 'evenement')]
    public function modifierEvenement(Evenement $evenement, Request $requete): Response
    {
        $formulaire = $this->createForm(EvenementType::class, $evenement)->handleRequest($requete);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('succes', 'Les modifications sont enregistrées.');

            return $this->redirectToRoute('partenaire_tableau_de_bord');
        }

        return $this->render('partenaire/evenement_formulaire.html.twig', ['formulaire' => $formulaire, 'evenement' => $evenement]);
    }

    #[Route('/evenements/{id<\d+>}/participants', name: 'partenaire_participants', methods: ['GET'])]
    #[IsGranted(EvenementVoter::GERER, 'evenement')]
    public function participants(Evenement $evenement, InscriptionRepository $inscriptions): Response
    {
        return $this->render('partenaire/participants.html.twig', [
            'evenement' => $evenement,
            'participants' => $inscriptions->participants($evenement),
        ]);
    }

    #[Route('/evenements/{id<\d+>}/sponsoriser', name: 'partenaire_sponsoriser', methods: ['POST'])]
    #[IsGranted(EvenementVoter::GERER, 'evenement')]
    #[IsCsrfTokenValid('sponsoriser')]
    public function sponsoriser(Evenement $evenement, Request $requete): Response
    {
        $formule = FormuleSponsoring::tryFrom($requete->getPayload()->getString('formule'));

        if (null === $formule || $evenement->estPasse()) {
            $this->addFlash('erreur', 'Cette mise en avant n\'est pas possible.');
        } else {
            $evenement->sponsoriser($formule);
            $this->entityManager->flush();
            $this->addFlash('succes', \sprintf('« %s » est mis en avant jusqu\'au %s.', $evenement->getTitre(), $evenement->getSponsorJusquAu()?->format('d/m à H\hi')));
        }

        return $this->redirectToRoute('partenaire_tableau_de_bord');
    }

    /** Le code peut venir d'un lecteur de QR (qui tape le texte lu) ou d'une saisie manuelle. */
    #[Route('/scan', name: 'partenaire_scan', methods: ['GET', 'POST'])]
    public function scan(Request $requete, ControleEntree $controle, #[CurrentUser] Utilisateur $partenaire): Response
    {
        $resultat = null;

        if ($requete->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('scan', $requete->getPayload()->getString('_token'))) {
                throw $this->createAccessDeniedException();
            }

            try {
                $inscription = $controle->valider($requete->getPayload()->getString('code'), $this->etablissement($partenaire));
                $resultat = ['succes' => true, 'inscription' => $inscription];
            } catch (ActionImpossible $refus) {
                $resultat = ['succes' => false, 'message' => $refus->getMessage()];
            }
        }

        return $this->render('partenaire/scan.html.twig', ['resultat' => $resultat]);
    }

    private function etablissement(Utilisateur $partenaire): Etablissement
    {
        return $partenaire->getEtablissementPrincipal()
            ?? throw $this->createNotFoundException('Aucun établissement n\'est rattaché à ce compte.');
    }
}

<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Catalogue;
use App\Entity\ClientCrm;
use App\Entity\Etablissement;
use App\Entity\Utilisateur;
use App\Enum\OffreClient;
use App\Enum\TypeCompte;
use App\Form\Model\DemandeInscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** Transforme une demande d'inscription validée en compte (et en établissement pour un pro). */
final readonly class CreationCompte
{
    /** Durée de l'essai offert à un nouveau partenaire. */
    private const DUREE_ESSAI = '+3 months';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $hacheur,
        private Journal $journal,
    ) {
    }

    public function creer(DemandeInscription $demande): Utilisateur
    {
        $utilisateur = (new Utilisateur())
            ->setType($demande->type)
            ->setPrenom((string) $demande->prenom)
            ->setNom((string) $demande->nom)
            ->setEmail((string) $demande->email)
            ->setDateNaissance($demande->dateNaissance)
            ->accepterCgu();

        $utilisateur->setPassword($this->hacheur->hashPassword($utilisateur, (string) $demande->motDePasse));

        if (TypeCompte::Etudiant === $demande->type) {
            $this->completerEtudiant($utilisateur, $demande);
        } else {
            $this->creerEtablissement($utilisateur, $demande);
        }

        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();

        $this->journal->enregistrer(Journal::INSCRIPTION_COMPTE, $utilisateur, ['type' => $demande->type->value]);

        return $utilisateur;
    }

    private function completerEtudiant(Utilisateur $utilisateur, DemandeInscription $demande): void
    {
        $utilisateur->setVille($demande->ville)->setInterets($demande->interets);

        // École et promo n'ont de sens que pour les 18-25 ans.
        if (($demande->age() ?? 0) <= Catalogue::AGE_MAXIMUM_ETUDIANT) {
            $utilisateur->setEcole($demande->ecole)->setPromo($demande->promo);
        }
    }

    /** Le partenaire arrive avec son établissement et une période d'essai ouverte dans le CRM. */
    private function creerEtablissement(Utilisateur $utilisateur, DemandeInscription $demande): void
    {
        $etablissement = (new Etablissement($utilisateur))
            ->setNom((string) $demande->nomEtablissement)
            ->setType($demande->typeEtablissement)
            ->setVille('Clermont-Ferrand');

        $client = ClientCrm::depuisEtablissement($etablissement);
        $client->demarrerEssai(OffreClient::Fondateur, new \DateTimeImmutable(self::DUREE_ESSAI));

        $this->entityManager->persist($etablissement);
        $this->entityManager->persist($client);
    }
}

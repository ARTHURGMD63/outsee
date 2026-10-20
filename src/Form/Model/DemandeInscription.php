<?php

declare(strict_types=1);

namespace App\Form\Model;

use App\Domain\Catalogue;
use App\Enum\TypeCompte;
use App\Enum\TypeLieu;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Données saisies à l'inscription. Le formulaire remplit cet objet, et
 * l'entité Utilisateur n'est créée qu'une fois tout validé.
 */
final class DemandeInscription
{
    #[Assert\Choice(choices: [TypeCompte::Etudiant, TypeCompte::Partenaire])]
    public TypeCompte $type = TypeCompte::Etudiant;

    #[Assert\NotBlank(message: 'Indique ton prénom.')]
    #[Assert\Length(max: 100)]
    public ?string $prenom = null;

    #[Assert\NotBlank(message: 'Indique ton nom.')]
    #[Assert\Length(max: 100)]
    public ?string $nom = null;

    #[Assert\NotBlank(message: 'Indique ton adresse e-mail.')]
    #[Assert\Email(message: 'Cette adresse e-mail n\'est pas valide.')]
    #[Assert\Length(max: 191)]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'Choisis un mot de passe.')]
    #[Assert\Length(min: 8, max: 4096, minMessage: 'Le mot de passe doit faire au moins {{ limit }} caractères.')]
    public ?string $motDePasse = null;

    /** L'âge se calcule à partir de la date : une case « je suis majeur » ne prouve rien. */
    #[Assert\NotNull(message: 'Indique ta date de naissance.')]
    #[Assert\LessThan('today', message: 'Cette date de naissance n\'est pas valide.')]
    #[Assert\GreaterThan('-120 years', message: 'Cette date de naissance ne semble pas correcte.')]
    public ?\DateTimeImmutable $dateNaissance = null;

    #[Assert\Choice(choices: Catalogue::VILLES)]
    public ?string $ville = 'Clermont-Ferrand';

    #[Assert\Choice(choices: Catalogue::ECOLES)]
    public ?string $ecole = null;

    #[Assert\Choice(choices: Catalogue::PROMOS)]
    public ?string $promo = null;

    /** @var list<string> */
    #[Assert\All([new Assert\Choice(choices: Catalogue::INTERETS)])]
    public array $interets = [];

    #[Assert\Length(max: 200)]
    public ?string $nomEtablissement = null;

    public TypeLieu $typeEtablissement = TypeLieu::Bar;

    #[Assert\IsTrue(message: 'Merci d\'accepter les conditions générales pour continuer.')]
    public bool $cgu = false;

    public function age(\DateTimeImmutable $aujourdhui = new \DateTimeImmutable('today')): ?int
    {
        return $this->dateNaissance?->diff($aujourdhui)->y;
    }

    #[Assert\Callback]
    public function verifier(ExecutionContextInterface $contexte): void
    {
        $age = $this->age();
        if (null !== $age && $age < Catalogue::AGE_MINIMUM) {
            $contexte->buildViolation('Outsee est réservée aux personnes majeures : l\'inscription n\'est pas possible avant 18 ans.')
                ->atPath('dateNaissance')
                ->addViolation();
        }

        if (TypeCompte::Partenaire === $this->type && '' === trim((string) $this->nomEtablissement)) {
            $contexte->buildViolation('Indique le nom de ton établissement.')
                ->atPath('nomEtablissement')
                ->addViolation();
        }
    }
}

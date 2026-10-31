<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Enum\TypeCompte;
use App\Form\Model\DemandeInscription;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DemandeInscriptionTest extends TestCase
{
    private ValidatorInterface $validateur;

    protected function setUp(): void
    {
        $this->validateur = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
    }

    private function demandeValide(): DemandeInscription
    {
        $demande = new DemandeInscription();
        $demande->prenom = 'Léa';
        $demande->nom = 'Dubois';
        $demande->email = 'lea@exemple.fr';
        $demande->motDePasse = 'un-mot-de-passe';
        $demande->dateNaissance = new \DateTimeImmutable('-20 years');
        $demande->cgu = true;

        return $demande;
    }

    /** @return list<string> chemins des champs en erreur */
    private function champsEnErreur(DemandeInscription $demande): array
    {
        $chemins = [];
        foreach ($this->validateur->validate($demande) as $violation) {
            $chemins[] = $violation->getPropertyPath();
        }

        return $chemins;
    }

    public function testUneDemandeCompleteEstValide(): void
    {
        self::assertSame([], $this->champsEnErreur($this->demandeValide()));
    }

    public function testUnMineurEstRefuse(): void
    {
        $demande = $this->demandeValide();
        $demande->dateNaissance = new \DateTimeImmutable('-17 years');

        self::assertContains('dateNaissance', $this->champsEnErreur($demande));
    }

    public function testLesCguDoiventEtreAcceptees(): void
    {
        $demande = $this->demandeValide();
        $demande->cgu = false;

        self::assertContains('cgu', $this->champsEnErreur($demande));
    }

    public function testUnMotDePasseTropCourtEstRefuse(): void
    {
        $demande = $this->demandeValide();
        $demande->motDePasse = 'court';

        self::assertContains('motDePasse', $this->champsEnErreur($demande));
    }

    public function testUnPartenaireDoitNommerSonEtablissement(): void
    {
        $demande = $this->demandeValide();
        $demande->type = TypeCompte::Partenaire;

        self::assertContains('nomEtablissement', $this->champsEnErreur($demande));
    }

    public function testUnInteretHorsCatalogueEstRefuse(): void
    {
        $demande = $this->demandeValide();
        $demande->interets = ['Running', 'Piratage'];

        self::assertContains('interets[1]', $this->champsEnErreur($demande));
    }
}

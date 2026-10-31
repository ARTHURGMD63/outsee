<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Etablissement;
use App\Entity\Evenement;
use App\Entity\Utilisateur;
use App\Enum\FormuleSponsoring;
use PHPUnit\Framework\TestCase;

final class EvenementTest extends TestCase
{
    private function evenement(): Evenement
    {
        return new Evenement(new Etablissement(new Utilisateur()));
    }

    public function testLePrixMembreDeduitLaReduction(): void
    {
        $evenement = $this->evenement()->setPrixNormal(12.0)->setReduction(25);

        self::assertSame(9.0, $evenement->getPrixMembre());
        self::assertSame(3.0, $evenement->getEconomie());
    }

    public function testUnEvenementGratuitNeCouteRienEtNeFaitRienEconomiser(): void
    {
        $evenement = $this->evenement()->setPrixNormal(10.0)->setReduction(50)->setGratuit(true);

        self::assertSame(0.0, $evenement->getPrixMembre());
        self::assertSame(10.0, $evenement->getEconomie());
    }

    public function testLaMiseEnAvantCourtDepuisLAchat(): void
    {
        $maintenant = new \DateTimeImmutable('2026-11-01 12:00');
        $evenement = $this->evenement()->setDateHeure(new \DateTimeImmutable('2026-12-01 22:00'));

        $evenement->sponsoriser(FormuleSponsoring::Top7, $maintenant);

        self::assertEquals(new \DateTimeImmutable('2026-11-08 12:00'), $evenement->getSponsorJusquAu());
        self::assertSame(49.0, $evenement->getSponsorTarif());
        self::assertTrue($evenement->isSponsoriseActif($maintenant));
    }

    public function testLaMiseEnAvantNeDepassePasLaSoiree(): void
    {
        $maintenant = new \DateTimeImmutable('2026-11-01 12:00');
        $soiree = new \DateTimeImmutable('2026-11-03 22:00');
        $evenement = $this->evenement()->setDateHeure($soiree);

        $evenement->sponsoriser(FormuleSponsoring::Premium30, $maintenant);

        self::assertEquals($soiree, $evenement->getSponsorJusquAu());
        self::assertFalse($evenement->isSponsoriseActif(new \DateTimeImmutable('2026-11-04')));
    }

    public function testUneSoireeSansFinEstConsidereeFinieSixHeuresApresLeDebut(): void
    {
        $evenement = $this->evenement()->setDateHeure(new \DateTimeImmutable('2026-11-01 22:00'));

        self::assertFalse($evenement->estPasse(new \DateTimeImmutable('2026-11-02 03:00')));
        self::assertTrue($evenement->estPasse(new \DateTimeImmutable('2026-11-02 05:00')));
    }
}

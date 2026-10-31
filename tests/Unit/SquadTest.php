<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Squad;
use App\Entity\Utilisateur;
use PHPUnit\Framework\TestCase;

final class SquadTest extends TestCase
{
    public function testLeCreateurEstMembreDOffice(): void
    {
        $createur = new Utilisateur();
        $squad = new Squad($createur);

        self::assertTrue($squad->estMembre($createur));
        self::assertCount(1, $squad->getMembres());
    }

    public function testOnNeRejointPasUneSquadComplete(): void
    {
        $squad = (new Squad(new Utilisateur()))->setQuota(2);
        $squad->rejoindre(new Utilisateur());

        $this->expectException(\DomainException::class);
        $squad->rejoindre(new Utilisateur());
    }

    public function testRejoindreDeuxFoisNeCreePasDeDoublon(): void
    {
        $squad = new Squad(new Utilisateur());
        $membre = new Utilisateur();

        $squad->rejoindre($membre);
        $squad->rejoindre($membre);

        self::assertCount(2, $squad->getMembres());
    }

    public function testLeCreateurNePeutPasQuitterSaSquad(): void
    {
        $createur = new Utilisateur();
        $squad = new Squad($createur);

        $this->expectException(\DomainException::class);
        $squad->quitter($createur);
    }

    public function testUnMembrePeutQuitter(): void
    {
        $squad = new Squad(new Utilisateur());
        $membre = new Utilisateur();
        $squad->rejoindre($membre);

        $squad->quitter($membre);

        self::assertFalse($squad->estMembre($membre));
    }
}

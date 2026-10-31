<?php

declare(strict_types=1);

namespace App\Tests\Fonctionnel;

use App\Entity\Evenement;
use App\Entity\Inscription;
use App\Enum\StatutInscription;
use App\Repository\EntreeJournalRepository;
use App\Repository\UtilisateurRepository;
use App\Service\Journal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EspacePartenaireTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $partenaire = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('jean@lebecquipique.fr');
        self::assertNotNull($partenaire);
        $this->client->loginUser($partenaire);
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testLeTableauDeBordListeLesSoireesDuLieu(): void
    {
        $this->client->request('GET', '/partenaire');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', 'Happy Hour');
        self::assertSelectorTextNotContains('main', 'Techno Underground');
    }

    public function testUnPartenaireNeModifiePasLesSoireesDUnAutreLieu(): void
    {
        $autre = $this->entityManager()->getRepository(Evenement::class)->findOneBy(['titre' => 'Latino Fever']);

        $this->client->request('GET', '/partenaire/evenements/'.$autre?->getId().'/modifier');

        self::assertResponseStatusCodeSame(403);
    }

    public function testPublierUneSoiree(): void
    {
        $this->client->request('GET', '/partenaire/evenements/nouveau');
        $this->client->submitForm('Publier', [
            'evenement[titre]' => 'Soirée Quiz',
            'evenement[type]' => 'bar',
            'evenement[dateHeure]' => (new \DateTimeImmutable('+10 days 20:00'))->format('Y-m-d\TH:i'),
            'evenement[quota]' => 50,
            'evenement[prixNormal]' => '8',
            'evenement[reduction]' => 25,
        ]);

        self::assertResponseRedirects('/partenaire');
        self::assertNotNull($this->entityManager()->getRepository(Evenement::class)->findOneBy(['titre' => 'Soirée Quiz']));
    }

    public function testScannerUnPassValideLEntreeUneSeuleFois(): void
    {
        $evenement = $this->entityManager()->getRepository(Evenement::class)->findOneBy(['titre' => 'Happy Hour jusqu\'à minuit']);
        $inscription = $this->entityManager()->getRepository(Inscription::class)->findOneBy(['evenement' => $evenement]);
        self::assertNotNull($inscription);
        $code = 'outsee:'.$inscription->getQrCode();

        $this->client->request('GET', '/partenaire/scan');
        $this->client->submitForm('Valider l\'entrée', ['code' => $code]);
        self::assertSelectorExists('.resultat-scan--ok');

        $this->client->submitForm('Valider l\'entrée', ['code' => $code]);
        self::assertSelectorTextContains('.resultat-scan--refus', 'déjà été scanné');

        $this->entityManager()->clear();
        $verifiee = $this->entityManager()->find(Inscription::class, $inscription->getId());
        self::assertSame(StatutInscription::Checkin, $verifiee?->getStatut());

        $journal = static::getContainer()->get(EntreeJournalRepository::class);
        self::assertArrayHasKey(Journal::ENTREE_VALIDEE, $journal->totauxParAction(1));
    }

    public function testUnPassInconnuEstRefuse(): void
    {
        $this->client->request('GET', '/partenaire/scan');
        $this->client->submitForm('Valider l\'entrée', ['code' => 'outsee:'.str_repeat('0', 64)]);

        self::assertSelectorTextContains('.resultat-scan--refus', 'aucune réservation');
    }
}

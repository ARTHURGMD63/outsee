<?php

declare(strict_types=1);

namespace App\Tests\Fonctionnel;

use App\Entity\Evenement;
use App\Entity\Inscription;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ParcoursEtudiantTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    private function connecter(string $email): void
    {
        $utilisateur = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail($email);
        self::assertNotNull($utilisateur);
        $this->client->loginUser($utilisateur);
    }

    private function evenement(string $titre): Evenement
    {
        $evenement = static::getContainer()->get(EntityManagerInterface::class)->getRepository(Evenement::class)->findOneBy(['titre' => $titre]);
        self::assertNotNull($evenement);

        return $evenement;
    }

    public function testUnVisiteurVoitLaPageDAccueil(): void
    {
        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Sors plus');
    }

    public function testLesPagesPriveesDemandentUneConnexion(): void
    {
        $this->client->request('GET', '/pass');

        self::assertResponseRedirects('/connexion');
    }

    public function testConnexionAvecLeFormulaire(): void
    {
        $this->client->request('GET', '/connexion');
        $this->client->submitForm('Se connecter', ['email' => 'lea@sigma.fr', 'mot_de_passe' => 'password']);

        self::assertResponseRedirects('/');
        $this->client->followRedirect();
        self::assertResponseRedirects('/evenements');
    }

    public function testUnMauvaisMotDePasseEstRefuse(): void
    {
        $this->client->request('GET', '/connexion');
        $this->client->submitForm('Se connecter', ['email' => 'lea@sigma.fr', 'mot_de_passe' => 'mauvais']);
        $this->client->followRedirect();

        self::assertSelectorExists('.form-error');
    }

    public function testInscriptionDUnEtudiant(): void
    {
        $this->client->request('GET', '/inscription');
        $this->client->submitForm('Créer mon compte', [
            'inscription[type]' => 'etudiant',
            'inscription[prenom]' => 'Nina',
            'inscription[nom]' => 'Martin',
            'inscription[email]' => 'nina@exemple.fr',
            'inscription[motDePasse]' => 'mot-de-passe-solide',
            'inscription[dateNaissance]' => (new \DateTimeImmutable('-20 years'))->format('Y-m-d'),
            'inscription[ville]' => 'Clermont-Ferrand',
            'inscription[cgu]' => true,
        ]);

        self::assertResponseRedirects('/evenements');
        $nina = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail('nina@exemple.fr');
        self::assertNotNull($nina);
        self::assertNotNull($nina->getCguAccepteesLe());
        self::assertNotSame('mot-de-passe-solide', $nina->getPassword());
    }

    public function testUnMineurNePeutPasSInscrire(): void
    {
        $this->client->request('GET', '/inscription');
        $this->client->submitForm('Créer mon compte', [
            'inscription[prenom]' => 'Tom',
            'inscription[nom]' => 'Petit',
            'inscription[email]' => 'tom@exemple.fr',
            'inscription[motDePasse]' => 'mot-de-passe-solide',
            'inscription[dateNaissance]' => (new \DateTimeImmutable('-16 years'))->format('Y-m-d'),
            'inscription[cgu]' => true,
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.champ-erreur', 'majeures');
    }

    public function testReserverPuisAnnulerUneSoiree(): void
    {
        $this->connecter('lucas@inp.fr');
        $evenement = $this->evenement('Techno Underground');

        $this->client->request('GET', '/evenements/'.$evenement->getId());
        $this->client->submitForm('Réserver ma place');
        self::assertResponseRedirects('/pass');

        $this->client->followRedirect();
        self::assertSelectorTextContains('main', 'Techno Underground');
        self::assertSelectorExists('.qr-pass svg');

        $this->client->request('GET', '/evenements/'.$evenement->getId());
        $this->client->submitForm('Annuler ma réservation');
        $this->client->followRedirect();
        self::assertSelectorExists('button:contains("Réserver ma place")');
    }

    public function testUneReservationSansJetonCsrfEstRefusee(): void
    {
        $this->connecter('lucas@inp.fr');
        $evenement = $this->evenement('Soirée Raclette');

        $this->client->request('POST', '/evenements/'.$evenement->getId().'/reserver', ['_token' => 'faux']);

        // Symfony traite un jeton invalide comme un échec d'authentification : retour à la connexion.
        self::assertResponseRedirects('/connexion');
        $inscription = static::getContainer()->get(EntityManagerInterface::class)->getRepository(Inscription::class)->findOneBy(['evenement' => $evenement]);
        self::assertNull($inscription);
    }

    public function testUnEtudiantNAccedePasALEspacePartenaire(): void
    {
        $this->connecter('arthur@uca.fr');
        $this->client->request('GET', '/partenaire');

        self::assertResponseStatusCodeSame(403);
    }
}

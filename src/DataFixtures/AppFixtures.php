<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\ClientCrm;
use App\Entity\Etablissement;
use App\Entity\Evenement;
use App\Entity\Inscription;
use App\Entity\Squad;
use App\Entity\SuiviUtilisateur;
use App\Entity\Utilisateur;
use App\Enum\NiveauSquad;
use App\Enum\OffreClient;
use App\Enum\StyleMusique;
use App\Enum\TypeCompte;
use App\Enum\TypeLieu;
use App\Enum\TypeSquad;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Jeu de démonstration, repris de l'ancienne installation. Les dates sont
 * relatives à maintenant : les événements sont toujours à venir.
 *
 * Mot de passe de tous les comptes : « password ».
 *   étudiant   : arthur@uca.fr
 *   partenaire : jean@lebecquipique.fr
 *   admin      : admin@outsee.fr
 */
final class AppFixtures extends Fixture
{
    private const MOT_DE_PASSE = 'password';

    public function __construct(private readonly UserPasswordHasherInterface $hacheur)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $etudiants = [];
        foreach ([
            ['Gramond', 'Arthur', 'arthur@uca.fr', 'UCA', 'L2', ['Sorties', 'Running', 'Boîtes', 'Gaming', 'Mixologie', 'Bars']],
            ['Dubois', 'Léa', 'lea@sigma.fr', 'SIGMA Clermont', 'M1', ['Running', 'Mixologie', 'Sorties']],
            ['Moreau', 'Lucas', 'lucas@inp.fr', 'INP Ingénieurs', 'L3', ['Gaming', 'Code', 'Bars']],
            ['Bonnet', 'Inès', 'ines@demo.fr', 'INP Ingénieurs', 'BUT2', ['Sorties', 'Boîtes', 'Techno']],
            ['Roy', 'Maxime', 'maxime@demo.fr', 'UCA', 'M2', ['Running', 'Foot', 'Bars']],
            ['Lopez', 'Sarah', 'sarah@demo.fr', 'UCA', 'L2', ['Mixologie', 'Bars', 'Sorties']],
            ['Bernard', 'Hugo', 'hugo@demo.fr', 'SIGMA Clermont', 'M2', ['Vélo', 'Sorties', 'Muscu']],
            ['Petit', 'Camille', 'camille@demo.fr', 'IFSI', 'L1', ['Boîtes', 'Techno', 'Musique']],
        ] as [$nom, $prenom, $email, $ecole, $promo, $interets]) {
            $etudiant = $this->compte($nom, $prenom, $email, TypeCompte::Etudiant)
                ->setEcole($ecole)
                ->setPromo($promo)
                ->setVille('Clermont-Ferrand')
                ->setInterets($interets);
            $manager->persist($etudiant);
            $etudiants[] = $etudiant;
        }

        $manager->persist($this->compte('Outsee', 'Équipe', 'admin@outsee.fr', TypeCompte::Admin));

        $bec = $this->etablissement($manager, 'Patron', 'Jean', 'jean@lebecquipique.fr', 'Le Bec qui Pique', TypeLieu::Bar, 'Place de Jaude');
        $barometre = $this->etablissement($manager, 'Gérant', 'Marie', 'marie@barometre.fr', 'Le Baromètre', TypeLieu::Boite, 'Montferrand');

        $evenements = [
            $this->evenement($bec, 'Happy Hour jusqu\'à minuit', 'Happy Hour prolongé pour les membres Outsee. Cocktails à moitié prix toute la soirée.', TypeLieu::Bar, StyleMusique::Generaliste, '+2 hours', 80, 50, 10.0),
            $this->evenement($bec, 'Beer Pong Tournament', 'Tournoi de beer pong par équipes de deux. Entrée gratuite, 50 € de consommations pour les gagnants.', TypeLieu::Afterwork, StyleMusique::Generaliste, '+5 hours', 40, 0, 0.0, gratuit: true),
            $this->evenement($bec, 'Mojito Night', '-60 % sur les cocktails tiki entre 19 h et 22 h. Ambiance tropicale, DJ set.', TypeLieu::Bar, StyleMusique::House, '+27 hours', 80, 60, 10.0),
            $this->evenement($barometre, 'Soirée Étudiante', 'Entrée gratuite avant 1 h avec ton pass Outsee. DJ set toute la nuit.', TypeLieu::Boite, StyleMusique::Generaliste, '+26 hours', 150, 100, 10.0),
            $this->evenement($bec, 'Blind Test Géant', 'Blind test par équipes, lots à gagner, shooters offerts aux gagnants.', TypeLieu::Bar, StyleMusique::Pop, '+4 days', 60, 20, 12.0),
            $this->evenement($barometre, 'Latino Fever', 'Initiation salsa à 22 h 30 puis reggaeton jusqu\'au bout de la nuit.', TypeLieu::Boite, StyleMusique::Latino, '+5 days', 150, 30, 12.0),
            $this->evenement($barometre, 'Techno Underground', 'Caves voûtées du centre, line-up underground, late session.', TypeLieu::Boite, StyleMusique::Techno, '+7 days', 220, 40, 15.0),
            $this->evenement($bec, 'Soirée Raclette', 'Raclette à volonté avec charcuterie d\'Auvergne. Idéal en groupe.', TypeLieu::Resto, StyleMusique::Sans, '+8 days', 30, 20, 17.0),
        ];
        foreach ($evenements as $evenement) {
            $manager->persist($evenement);
        }

        foreach ([[0, 0], [0, 3], [1, 0], [2, 0], [3, 0], [4, 3], [5, 3], [3, 5]] as [$etudiant, $evenement]) {
            $manager->persist(new Inscription($etudiants[$etudiant], $evenements[$evenement]));
        }

        foreach ([
            [0, TypeSquad::Running, 'Puy-de-Dôme sunset', 'Sortie running avec vue sur le Puy-de-Dôme au coucher du soleil. 8 km à allure confortable.', NiveauSquad::Intermediaire, '+2 days 18:30', 'Départ parking de Royat', 10, [1, 2]],
            [1, TypeSquad::Muscu, 'Push day · Basic Fit', 'Séance pectoraux, épaules, triceps. On se retrouve à l\'entrée.', NiveauSquad::Tous, '+1 day 18:00', 'Basic Fit Clermont', 6, [2]],
            [2, TypeSquad::Velo, 'Tour du lac d\'Aydat', '22 km autour du lac, 180 m de dénivelé. Sortie tranquille.', NiveauSquad::Debutant, '+3 days 10:00', 'Parking du lac d\'Aydat', 15, [0, 1]],
            [3, TypeSquad::Running, 'Footing du matin', 'Petit footing au parc avant les cours. Tous niveaux bienvenus.', NiveauSquad::Debutant, '+4 days 07:30', 'Jardin Lecoq', 12, [0]],
        ] as [$createur, $type, $titre, $description, $niveau, $date, $lieu, $quota, $membres]) {
            $squad = (new Squad($etudiants[$createur]))
                ->setType($type)
                ->setTitre($titre)
                ->setDescription($description)
                ->setNiveau($niveau)
                ->setDateHeure(new \DateTimeImmutable($date))
                ->setLieu($lieu)
                ->setQuota($quota);
            foreach ($membres as $membre) {
                $squad->rejoindre($etudiants[$membre]);
            }
            $manager->persist($squad);
        }

        foreach ([[0, 1], [0, 4], [1, 0], [4, 0], [5, 0], [6, 0]] as [$abonne, $suivi]) {
            $suiviUtilisateur = new SuiviUtilisateur($etudiants[$abonne], $etudiants[$suivi]);
            $suiviUtilisateur->accepter();
            $manager->persist($suiviUtilisateur);
        }
        // Une demande en attente, pour que l'écran d'acceptation ait quelque chose à montrer.
        $manager->persist(new SuiviUtilisateur($etudiants[6], $etudiants[1]));

        $manager->flush();
    }

    private function compte(string $nom, string $prenom, string $email, TypeCompte $type): Utilisateur
    {
        $utilisateur = (new Utilisateur())
            ->setNom($nom)
            ->setPrenom($prenom)
            ->setEmail($email)
            ->setType($type)
            ->setDateNaissance(new \DateTimeImmutable('-21 years'))
            ->accepterCgu();

        return $utilisateur->setPassword($this->hacheur->hashPassword($utilisateur, self::MOT_DE_PASSE));
    }

    private function etablissement(ObjectManager $manager, string $nom, string $prenom, string $email, string $nomLieu, TypeLieu $type, string $adresse): Etablissement
    {
        $partenaire = $this->compte($nom, $prenom, $email, TypeCompte::Partenaire);
        $etablissement = (new Etablissement($partenaire))
            ->setNom($nomLieu)
            ->setType($type)
            ->setAdresse($adresse.', Clermont-Ferrand');

        $client = ClientCrm::depuisEtablissement($etablissement);
        $client->demarrerEssai(OffreClient::Fondateur, new \DateTimeImmutable('+3 months'));

        $manager->persist($partenaire);
        $manager->persist($etablissement);
        $manager->persist($client);

        return $etablissement;
    }

    private function evenement(
        Etablissement $etablissement,
        string $titre,
        string $description,
        TypeLieu $type,
        StyleMusique $style,
        string $quand,
        int $quota,
        int $reduction,
        float $prix,
        bool $gratuit = false,
    ): Evenement {
        return (new Evenement($etablissement))
            ->setTitre($titre)
            ->setDescription($description)
            ->setType($type)
            ->setStyleMusique($style)
            ->setDateHeure(new \DateTimeImmutable($quand))
            ->setQuota($quota)
            ->setReduction($reduction)
            ->setPrixNormal($prix)
            ->setGratuit($gratuit);
    }
}

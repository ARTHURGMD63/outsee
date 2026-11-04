# Outsee

Les sorties étudiantes de Clermont-Ferrand, avec des réductions réservées aux membres.
Les étudiants réservent une soirée et présentent leur pass (QR code) à l'entrée. Ils retrouvent d'autres étudiants en « squads » sportives.
Les bars et boîtes partenaires publient leurs soirées et scannent les pass. Un back-office suit l'activité et le commercial.

## Technologies

| Rôle | Choix |
| --- | --- |
| Langage | PHP 8.3 |
| Framework | Symfony 7.4 (version LTS, maintenue jusqu'en 2029) |
| Base relationnelle | MariaDB 11.4 avec Doctrine ORM et Doctrine Migrations |
| Base NoSQL | MongoDB 7 avec Doctrine MongoDB ODM |
| Affichage | Twig, AssetMapper, Stimulus (aucune étape de build JavaScript) |
| Tests | PHPUnit (tests unitaires et fonctionnels) |
| Qualité | PHPStan niveau 6, PHP CS Fixer (règles Symfony) |
| Environnement | Docker Compose : application, MariaDB, MongoDB, Mailpit |
| Intégration continue | GitHub Actions |

## Lancer le projet

Seul Docker est nécessaire.

```bash
docker compose up -d --build
docker compose exec -u www-data app composer install
docker compose exec -u www-data app php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec -u www-data app php bin/console doctrine:mongodb:schema:create
docker compose exec -u www-data app php bin/console doctrine:fixtures:load --append --no-interaction
```

- Application : http://localhost:8080
- Mails envoyés (Mailpit) : http://localhost:8025

Comptes de démonstration, mot de passe `password` :

| Rôle | E-mail |
| --- | --- |
| Étudiant | `arthur@uca.fr` |
| Partenaire | `jean@lebecquipique.fr` |
| Administrateur | `admin@outsee.fr` |

## Tests et qualité

```bash
docker compose exec -u www-data app php bin/phpunit
docker compose exec -u www-data app vendor/bin/phpstan analyse --memory-limit=1G
docker compose exec -u www-data app vendor/bin/php-cs-fixer fix --dry-run --diff
```

La base de test (`outsee_test`) est recréée à chaque lancement de PHPUnit. GitHub Actions lance les mêmes contrôles à chaque push et à chaque Pull Request.

## Organisation du code

```
src/
├── Controller/      une classe par espace : événements, squads, pass, profil, partenaire, admin
├── Entity/          entités Doctrine (MariaDB)
├── Document/        documents Doctrine ODM (MongoDB)
├── Enum/            valeurs fixes : statuts, types, formules
├── Form/            formulaires Symfony
├── Repository/      requêtes
├── Service/         règles métier : réservation, contrôle d'entrée, gamification, journal
├── Security/        vérification des comptes et voter des événements
└── Twig/            icônes et QR code des pass
```

Les contrôleurs restent courts : ils lisent la requête, appellent un service et choisissent la page à afficher. Les règles métier se trouvent dans les services et les entités, où les tests les vérifient sans navigateur.

## Documentation

- [Bases de données](docs/BASES_DE_DONNEES.md) : modèle relationnel, rôle de MongoDB, index et migrations.
- [Sécurité](docs/SECURITE.md) : authentification, droits d'accès et protections.

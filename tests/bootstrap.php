<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

/*
 * Base de test remise à zéro à chaque lancement : schéma recréé, jeu de
 * démonstration chargé, collection MongoDB vidée. Les tests partent tous
 * du même état connu.
 */
$console = static function (string $commande, bool $echecTolere = false): void {
    passthru(sprintf('php %s %s --env=test --no-interaction --quiet', escapeshellarg(dirname(__DIR__).'/bin/console'), $commande), $code);
    if (0 !== $code && !$echecTolere) {
        throw new RuntimeException("Préparation de la base de test impossible : $commande");
    }
};

$console('doctrine:database:create --if-not-exists');
$console('doctrine:schema:drop --force --full-database');
$console('doctrine:migrations:migrate');
$console('doctrine:fixtures:load --append');
// Échoue si la base MongoDB n'existe pas encore (premier lancement) : sans gravité.
$console('doctrine:mongodb:schema:drop', true);
$console('doctrine:mongodb:schema:create');

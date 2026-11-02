#!/bin/sh
# Le cache et les journaux appartiennent à l'utilisateur d'Apache, quel que
# soit celui qui a lancé la dernière commande console.
set -e
mkdir -p var vendor
chown -R www-data:www-data var vendor
exec docker-php-entrypoint "$@"

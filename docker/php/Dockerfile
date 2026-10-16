# Image de développement Outsee : PHP 8.3, Apache, et les extensions dont
# Symfony et MongoDB ont besoin.
FROM php:8.3-apache

RUN apt-get update \
 && apt-get install -y --no-install-recommends git unzip libicu-dev libzip-dev libssl-dev \
 && docker-php-ext-install intl pdo_mysql zip opcache \
 && pecl install mongodb-2.5.6 \
 && docker-php-ext-enable mongodb \
 && rm -rf /var/lib/apt/lists/*

# Apache ne sert que le dossier public/ ; toute adresse qui n'est pas un
# fichier réel est confiée à Symfony (front controller).
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
COPY apache.conf /etc/apache2/conf-enabled/symfony.conf
COPY php.ini /usr/local/etc/php/conf.d/outsee.ini
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_HOME=/tmp/composer

COPY entrypoint.sh /usr/local/bin/outsee-entrypoint
RUN chmod +x /usr/local/bin/outsee-entrypoint
ENTRYPOINT ["outsee-entrypoint"]
CMD ["apache2-foreground"]

WORKDIR /var/www/html

FROM php:8.3-fpm-alpine

RUN apk add --no-cache unzip curl

# Composer aus offiziellem Image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Abhängigkeiten installieren (nur composer.json, kein contact.php — kommt per Volume-Mount)
COPY composer.json ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

# contact.php wird per Volume-Mount eingebunden (siehe docker-compose-vps.yml)

USER nobody

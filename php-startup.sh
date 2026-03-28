#!/bin/sh
set -e

apk add --no-cache git unzip curl

# Composer installieren
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Code von GitHub holen
git clone --depth 1 https://github.com/CmdKratosDev/cyber-aspis-website.git /tmp/ca-repo
mkdir -p /var/www/html
cp /tmp/ca-repo/contact.php /var/www/html/contact.php
cp /tmp/ca-repo/composer.json /var/www/html/composer.json
rm -rf /tmp/ca-repo

# PHPMailer installieren
cd /var/www/html
composer install --no-dev --optimize-autoloader --no-interaction

# SMTP-Passwort in Datei schreiben (vermeidet PHP-FPM INI-Parser Probleme mit Sonderzeichen)
printf '%s' "$SMTP_PASS" > /run/smtp_pass
chmod 600 /run/smtp_pass

exec php-fpm

#!/bin/sh

echo "===========>"

# Changer les permissions
chown -R www-data:www-data /var/www/images-optim
rm -rf /var/www/html

# Installer les dépendances Composer
composer install --working-dir=/var/www

# Démarrer php-fpm
php-fpm

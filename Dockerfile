# Utiliser l'image PHP 8 comme base
FROM php:8-fpm

# Répertoire par défaut
WORKDIR /var/www

# Mettre à jour le système et installer les dépendances requises
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    git \
    unzip

# Installer les extensions PHP nécessaires
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Installer les librairies pour l'optimisation d'image
RUN apt-get install -y \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle

# Exposer le port 1234 pour PHP-FPM
EXPOSE 1234

# Copier le script entrypoint.sh
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

# Utiliser entrypoint.sh comme entrypoint
ENTRYPOINT ["entrypoint.sh"]
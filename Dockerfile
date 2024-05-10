# Use the PHP 8 image as the base
FROM php:8-fpm

# Default directory
WORKDIR /var/www

# Update the system and install the required dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    git \
    unzip

# Install the necessary PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install libraries for image optimization
RUN apt-get install -y \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle

# Expose port 1234 for PHP-FPM
EXPOSE 1234

# Copy the entrypoint.sh script
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

# Copy the swagger documentation into the image
COPY swagger.yaml /var/www/swagger.yaml

# Use entrypoint.sh as the entrypoint
ENTRYPOINT ["entrypoint.sh"]

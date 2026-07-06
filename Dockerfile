# Use the official PHP image with Apache pre-installed
FROM php:8.4-apache

# Install mysqli or pdo_mysql extensions for your database
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy your local PHP files into the Apache web directory
COPY . /var/www/html/

# Enable Apache mod_rewrite if your app uses custom routing/.htaccess
RUN a2enmod rewrite

# Expose port 80 so Render can direct traffic to it
EXPOSE 80

FROM php:8.2-apache

# Install system dependencies needed for Composer and ZIP handling
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql zip

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy your code into the container
COPY . /var/www/html/

# Run composer installation inside the container if a composer.json exists
WORKDIR /var/www/html
RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader; fi

EXPOSE 80

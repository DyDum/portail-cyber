FROM php:8.3-fpm

# ------------------------
# 1. Installer dépendances système
# ------------------------
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev libldap2-dev libssl-dev libsasl2-dev zip curl \
    && docker-php-ext-configure ldap --with-ldap=/usr --with-ldap-sasl \
    && docker-php-ext-install intl pdo pdo_mysql opcache ldap \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ------------------------
# 2. Installer Composer globalement
# ------------------------
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# ------------------------
# 3. Définir le dossier de travail
# ------------------------
WORKDIR /var/www/html

# ------------------------
# 4. Copier le projet Symfony
# ------------------------
COPY ./symfony /var/www/html

# ------------------------
# 5. Installer les dépendances Symfony
# ------------------------
RUN composer install --no-interaction --prefer-dist --optimize-autoloader || true

# ------------------------
# 6. Installer automatiquement les composants utiles
# ------------------------
RUN composer require symfony/twig-bundle symfony/asset php-feed-io/feed-io guzzlehttp/guzzle php-http/guzzle7-adapter nyholm/psr7 psr/log symfony/ldap symfony/security-bundle --no-interaction || true

# ------------------------
# 7. Droits et port
# ------------------------
RUN chown -R www-data:www-data /var/www/html
EXPOSE 9000

# ------------------------
# 8. Lancer PHP-FPM
# ------------------------
CMD ["php-fpm"]

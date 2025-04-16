FROM php:8.4-cli

# Zainstaluj potrzebne rozszerzenia
RUN apt-get update && apt-get install -y \
    git unzip zip curl libzip-dev libonig-dev libxml2-dev mariadb-client \
    && docker-php-ext-install pdo pdo_mysql zip

# Symfony CLI (opcjonalnie)
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

WORKDIR /var/www/html


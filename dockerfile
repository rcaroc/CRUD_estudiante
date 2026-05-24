FROM php:8.2-apache

# Instalar extensión PostgreSQL PDO
RUN apt-get update && docker-php-ext-install pdo pdo_pgsql

# Copiar archivos al servidor Apache
COPY . /var/www/html/

# Dar permisos
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
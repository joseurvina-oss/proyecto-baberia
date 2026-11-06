FROM php:8.2-apache

# Instalar extensiones necesarias incluyendo mysqli
RUN docker-php-ext-install mysqli pdo_mysql

# Desactivar MPMs conflictivos y habilitar mpm_prefork
RUN a2dismod mpm_event mpm_worker && a2enmod mpm_prefork

COPY . /var/www/html/
EXPOSE 80
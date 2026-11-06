FROM php:8.2-apache

# Instalar extensiones de base de datos necesarias
RUN docker-php-ext-install mysqli pdo_mysql

# Deshabilitar event/worker de forma limpia y habilitar prefork
RUN a2dismod -f mpm_event mpm_worker && a2enmod mpm_prefork

# Copiar el código fuente de tu aplicación al servidor web
COPY . /var/www/html/

EXPOSE 80
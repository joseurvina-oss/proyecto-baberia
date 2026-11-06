FROM php:8.2-apache

# Instalar extensiones de base de datos
RUN docker-php-ext-install mysqli pdo_mysql

# Forzar el uso exclusivo de mpm_prefork eliminando los otros módulos
RUN rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* \
    && a2enmod mpm_prefork

# Copiar el código fuente de tu aplicación al servidor web
COPY . /var/www/html/

EXPOSE 80
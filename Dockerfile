# Usar la imagen oficial de PHP con Apache integrado
FROM php:8.2-apache

# Instalar extensiones de base de datos para MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilitar el módulo rewrite por si luego usas URLs amigables
RUN a2enmod rewrite

# Copiar todos los archivos de tu barbería a la carpeta web de Apache
COPY . /var/www/html/

# Dar permisos correctos a los archivos
RUN chown -R www-data:www-data /var/www/html

# Reemplazar el puerto 80 por defecto con el puerto dinámico que exige Railway ($PORT)
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Apache arranca automáticamente, no necesitamos comandos extra
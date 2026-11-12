# Usar la imagen oficial de PHP con Apache integrado
FROM php:8.2-apache

# Instalar extensiones de base de datos
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Deshabilitar MPMs conflictivos y habilitar mpm_prefork (requerido por mod_php)
RUN a2dismod mpm_event mpm_worker && a2enmod mpm_prefork

# Copiar archivos
COPY . /var/www/html/

# Dar permisos
RUN chown -R www-data:www-data /var/www/html

# Configurar puerto dinámico de Railway
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
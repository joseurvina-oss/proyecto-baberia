FROM php:8.2-apache

# Instalar extensiones de base de datos
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Eliminar físicamente los módulos conflictivos para que no se carguen por error
RUN rm -f /etc/apache2/mods-available/mpm_event.load \
    && rm -f /etc/apache2/mods-available/mpm_worker.load \
    && rm -f /etc/apache2/mods-enabled/mpm_event.load \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.load \
    && a2enmod mpm_prefork

# Copiar todos tus archivos al servidor web
COPY . /var/www/html/

# Permisos
RUN chown -R www-data:www-data /var/www/html

# Configurar puerto dinámico de Railway
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
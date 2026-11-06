FROM php:8.2-fpm

# Instalar extensiones de base de datos necesarias
RUN docker-php-ext-install mysqli pdo_mysql

# Instalar Nginx y gettext-base (para usar envsubst)
RUN apt-get update && apt-get install -y nginx gettext-base

# Copiar el código fuente
COPY . /var/www/html/

# Crear una plantilla de configuración de Nginx usando $PORT
RUN echo 'server { \
    listen 80; \
    root /var/www/html; \
    index index.php index.html; \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    location ~ \.php$ { \
        include fastcgi_params; \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_index index.php; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
    } \
}' > /etc/nginx/sites-available/default

EXPOSE 80

# Script para reemplazar el puerto por defecto de Railway y arrancar los servicios
CMD sed -i "s/listen 80;/listen $PORT;/g" /etc/nginx/sites-available/default && service nginx start && php-fpm
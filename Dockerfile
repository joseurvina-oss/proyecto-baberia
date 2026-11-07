FROM php:8.2-fpm

# Instalar extensiones de base de datos necesarias
RUN docker-php-ext-install mysqli pdo_mysql

# Instalar Nginx
RUN apt-get update && apt-get install -y nginx

# Copiar el código fuente de tu aplicación
COPY . /var/www/html/

# Crear la carpeta para el socket de PHP
RUN mkdir -p /run/php

# Configurar Nginx para que se comunique mediante socket Unix y use el puerto dinámico de Railway
RUN echo 'server { \
    listen __PORT__; \
    root /var/www/html; \
    index index.php index.html; \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    location ~ \.php$ { \
        include fastcgi_params; \
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock; \
        fastcgi_index index.php; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
    } \
}' > /etc/nginx/sites-available/default

# Configurar PHP-FPM para que escuche en el socket Unix
RUN sed -i "s|listen = 127.0.0.1:9000|listen = /var/run/php/php8.2-fpm.sock|g" /usr/local/etc/php-fpm.d/www.conf \
    && sed -i "s|;listen.owner = www-data|listen.owner = www-data|g" /usr/local/etc/php-fpm.d/www.conf \
    && sed -i "s|;listen.group = www-data|listen.group = www-data|g" /usr/local/etc/php-fpm.d/www.conf

EXPOSE 80

# Inyectar el puerto dinámico de Railway y arrancar PHP-FPM y Nginx
CMD sed -i "s/__PORT__/$PORT/g" /etc/nginx/sites-available/default && php-fpm -D && nginx -g "daemon off;"
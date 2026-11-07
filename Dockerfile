FROM php:8.2-fpm

# Instalar extensiones de base de datos necesarias
RUN docker-php-ext-install mysqli pdo_mysql

# Instalar Nginx
RUN apt-get update && apt-get install -y nginx

# Copiar el código fuente al servidor web
COPY . /var/www/html/

# Configurar Nginx para que use la variable $PORT de Railway
RUN echo 'server { \
    listen __PORT__; \
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

# Reemplazar __PORT__ por la variable de entorno $PORT y arrancar los servicios
CMD sed -i "s/__PORT__/$PORT/g" /etc/nginx/sites-available/default && service nginx start && php-fpm -F
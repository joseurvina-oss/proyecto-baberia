FROM php:8.2-fpm

# Instalar extensiones de base de datos necesarias
RUN docker-php-ext-install mysqli pdo_mysql

# Instalar Nginx
RUN apt-get update && apt-get install -y nginx

# Copiar el código fuente al directorio web
COPY . /var/www/html/

# Configurar Nginx para que apunte a la carpeta del proyecto
RUN echo 'server { \
    listen ${PORT}; \
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
# Usar imagen ligera de PHP puro
FROM php:8.2-cli

# Instalar utilidades necesarias para Composer y la conexión a MySQL
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer desde su imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir la carpeta de trabajo
WORKDIR /app

# Copiar los archivos de dependencias primero (aprovecha la caché de Docker)
COPY composer.json composer.lock ./

# Instalar las dependencias en /app/vendor
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copiar el resto de tus archivos al contenedor
COPY . /app

# Arrancar el servidor web integrado de PHP usando el puerto dinámico de Railway
CMD ["sh", "-c", "php -S 0.0.0.0:$PORT"]
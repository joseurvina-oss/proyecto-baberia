# Usar imagen ligera de PHP puro (sin Apache ni Nginx)
FROM php:8.2-cli

# Instalar la conexión a la base de datos MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Definir la carpeta de trabajo
WORKDIR /app

# Copiar todos tus archivos al contenedor
COPY . /app

# Arrancar el servidor web integrado de PHP usando el puerto dinámico de Railway
CMD ["sh", "-c", "php -S 0.0.0.0:$PORT"]
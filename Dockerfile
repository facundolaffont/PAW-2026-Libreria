# Instala el cliente de PHP.
FROM php:8.2.30-cli-alpine3.22

# Instala extensiones PDO MySQL y cURL.
RUN apk add --no-cache curl-dev \
    && docker-php-ext-install pdo pdo_mysql curl

# Instala Composer en la imagen: copia desde la imagen de Composer oficial.
COPY --from=composer:2.9.7 /usr/bin/composer /usr/bin/composer
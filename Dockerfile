# Usa una imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instala la extensión pdo_pgsql para conectar con PostgreSQL (Neon)
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Configura Apache para que escuche en el puerto 10000 (el que usa Render)
RUN sed -ri 's/^Listen 80$/Listen 10000/' /etc/apache2/ports.conf \
    && sed -ri 's/<VirtualHost \*:80>/<VirtualHost *:10000>/' /etc/apache2/sites-available/000-default.conf

# Copia tu código al contenedor
WORKDIR /var/www/html
COPY . /var/www/html/

# Expone el puerto 10000
EXPOSE 10000
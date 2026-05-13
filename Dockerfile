FROM php:8.3-apache

WORKDIR /var/www/html

RUN apt-get update \
  && apt-get install -y --no-install-recommends sqlite3 libsqlite3-dev \
  && docker-php-source extract \
  && docker-php-ext-install pdo_sqlite \
  && docker-php-source delete \
  && rm -rf /var/lib/apt/lists/*

COPY index.html /var/www/html/index.html
COPY backend.php /var/www/html/backend.php

RUN mkdir -p /var/www/html/data \
  && chown -R www-data:www-data /var/www/html

EXPOSE 80

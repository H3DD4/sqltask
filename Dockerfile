FROM php:8.3-apache

WORKDIR /app

RUN apt-get update \
  && apt-get install -y --no-install-recommends sqlite3 libsqlite3-dev \
  && docker-php-source extract \
  && docker-php-ext-install pdo_sqlite \
  && docker-php-source delete \
  && rm -rf /var/lib/apt/lists/*

COPY index.html /app/index.html
COPY backend.php /app/backend.php

EXPOSE 80

FROM php:8.3-cli

WORKDIR /app

RUN apt-get update \
  && apt-get install -y --no-install-recommends sqlite3 \
  && docker-php-ext-install sqlite3 pdo_sqlite \
  && rm -rf /var/lib/apt/lists/*

COPY index.html /app/index.html
COPY backend.php /app/backend.php

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "/app"]

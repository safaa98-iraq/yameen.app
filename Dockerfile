# Demo image (Render and similar): Apache + PHP 8.3 with SQLite.
# For the live site follow the README deployment steps with MySQL instead.

# 1. Arabic font files from google/fonts (subset to Arabic + basic Latin).
FROM python:3.12-slim AS fonts
RUN apt-get update && apt-get install -y --no-install-recommends git && rm -rf /var/lib/apt/lists/*
WORKDIR /app
COPY scripts/fonts ./scripts/fonts
COPY resources/fonts ./resources/fonts
RUN pip install --no-cache-dir -r scripts/fonts/requirements.txt \
 && python scripts/fonts/build.py --out /fonts --pairings resources/fonts/pairings.json \
 && rm -rf /root/.cache

# 2. PHP dependencies.
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --no-dev

# 3. Front-end build (also builds the Pro-only modules into storage/app/pro).
FROM node:22-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# 4. Runtime.
FROM php:8.3-apache
RUN apt-get update && apt-get install -y --no-install-recommends libicu-dev libsqlite3-dev \
 && docker-php-ext-install intl pdo_sqlite opcache \
 && rm -rf /var/lib/apt/lists/* \
 && a2enmod rewrite headers \
 && sed -ri 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf \
 && sed -ri 's#AllowOverride None#AllowOverride All#g' /etc/apache2/apache2.conf
WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY --from=assets /app/storage/app/pro ./storage/app/pro
COPY --from=fonts /fonts ./storage/app/fonts
RUN chown -R www-data:www-data storage bootstrap/cache database \
 && chmod +x docker/start.sh
CMD ["docker/start.sh"]

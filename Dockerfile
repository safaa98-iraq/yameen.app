# Demo image (Render and similar): Apache + PHP 8.3 with SQLite.
# For the live site follow the README deployment steps with MySQL instead.

# 1. Arabic font files: the exact build published by CI on the gh-pages branch
#    (building them here takes too long). For a server without this, run
#    `php artisan fonts:sync` instead.
FROM alpine:3.20 AS fonts
ARG FONTS_TARBALL=https://codeload.github.com/safaa98-iraq/yameen.app/tar.gz/refs/heads/gh-pages
RUN apk add --no-cache curl tar \
 && mkdir -p /fonts /tmp/site \
 && curl -fsSL "$FONTS_TARBALL" | tar -xz -C /tmp/site --strip-components=1 \
 && mv /tmp/site/font-files /fonts/files \
 && mv /fonts/files/fonts.json /fonts/fonts.json \
 && rm -rf /tmp/site

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

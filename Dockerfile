# syntax=docker/dockerfile:1
# Image produksi sederhana: Apache + PHP 8.3. Build asset Vite dilakukan di stage terpisah.

# ---- Stage 1: dependency PHP ----
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction
COPY . .
RUN composer dump-autoload --no-dev --optimize --no-scripts

# ---- Stage 2: build asset (Tailwind + Vite) ----
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm install
COPY resources ./resources
COPY app/Enums ./app/Enums
COPY vite.config.js tailwind.config.js postcss.config.js ./
RUN npm run build

# ---- Stage 3: runtime ----
FROM php:8.3-apache AS app

RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev libicu-dev unzip curl \
    && docker-php-ext-install pdo_mysql zip intl bcmath opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && printf 'ServerTokens Prod\nServerSignature Off\n' > /etc/apache2/conf-available/security-extra.conf \
    && a2enconf security-extra

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-katniss.ini

WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

HEALTHCHECK --interval=30s --timeout=5s --retries=3 CMD curl -fsS http://localhost/health || exit 1

EXPOSE 80
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]

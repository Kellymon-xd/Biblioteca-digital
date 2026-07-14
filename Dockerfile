FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_mysql gd \
    && a2enmod rewrite headers \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}/!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY docker/php/custom.ini /usr/local/etc/php/conf.d/biblioteca.ini
COPY docker/apache/security.conf /etc/apache2/conf-available/biblioteca-security.conf
RUN a2enconf biblioteca-security

WORKDIR /var/www/html
COPY . /var/www/html

RUN mkdir -p /var/www/html/public/uploads/libros/orig /var/www/html/public/uploads/libros/thumb \
    && chown -R www-data:www-data /var/www/html/public/uploads \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && chmod -R 775 /var/www/html/public/uploads

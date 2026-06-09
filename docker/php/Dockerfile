FROM php:8.4-fpm-alpine

# System deps
RUN apk add --no-cache \
        bash \
        git \
        curl \
        zip \
        unzip \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
        sqlite \
        sqlite-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        autoconf \
        g++ \
        make \
        linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_sqlite \
        mbstring \
        intl \
        bcmath \
        zip \
        gd \
        opcache \
        pcntl \
    && apk del --no-cache autoconf g++ make linux-headers \
    && rm -rf /tmp/pear

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Sensible PHP-FPM tuning for a small landing page
RUN { \
        echo 'memory_limit=256M'; \
        echo 'upload_max_filesize=20M'; \
        echo 'post_max_size=20M'; \
        echo 'max_execution_time=60'; \
        echo 'date.timezone=Europe/Athens'; \
    } > /usr/local/etc/php/conf.d/zz-custom.ini

# Match host UID/GID so volume-mounted files don't end up root-owned
ARG UID=1000
ARG GID=1000
RUN addgroup -g ${GID} app \
    && adduser -D -u ${UID} -G app -s /bin/bash app \
    && mkdir -p /var/www/html \
    && chown -R app:app /var/www/html

WORKDIR /var/www/html

USER app

EXPOSE 9000

CMD ["php-fpm"]

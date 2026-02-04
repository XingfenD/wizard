FROM php:7.4-cli

RUN echo "memory_limit=-1" > "$PHP_INI_DIR/conf.d/memory-limit.ini" \
&& echo "date.timezone=${PHP_TIMEZONE:-UTC}" > "$PHP_INI_DIR/conf.d/date_timezone.ini"

RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        libldap-dev \
        libzip-dev \
        wget \
        curl \
        git \
        subversion \
        zip \
        unzip \
        mercurial \
        --no-install-recommends && rm -r /var/lib/apt/lists/* \
    && docker-php-ext-install -j$(nproc) pcntl exif pdo_mysql zip ldap \
    && docker-php-ext-configure gd \
    && docker-php-ext-install -j$(nproc) gd
RUN wget https://getcomposer.org/download/2.5.0/composer.phar \
    && mv composer.phar /usr/bin/composer.phar \
    && chmod +x /usr/bin/composer.phar \
    && ln -s /usr/bin/composer.phar /usr/bin/composer \
    && php /usr/bin/composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/

WORKDIR /webroot/wizard
COPY ./ /webroot/wizard

COPY ./init.sh /opt/init.sh
CMD ["bash", "/opt/init.sh"]

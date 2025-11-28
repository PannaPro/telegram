FROM php:8.4-fpm-bookworm as base

WORKDIR /app

RUN apt update && apt install -y --no-install-recommends \
    acl \
    file \
    gettext \
    git \
    curl \
    openssl \
    && rm -rf /var/lib/apt/lists/*

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN set -eux; \
	install-php-extensions \
      @composer \
      apcu \
      intl \
      opcache \
      pdo_pgsql \
      mbstring \
      zip \
      amqp \
      dom \
      redis \
      && rm /usr/local/bin/install-php-extensions \
      && rm -rf /var/lib/apt/lists/* \
    ;

FROM base as dev

RUN apt update && apt install -y --no-install-recommends \
    rsync \
    vim \
    nano \
    && rm -rf /var/lib/apt/lists/*

#RUN set -eux; \
#	install-php-extensions \
#      xdebug \
#      pcov \
#    ;

RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash
RUN apt install -y --no-install-recommends symfony-cli

ARG HOST_UID=1000
ARG HOST_GID=1000

RUN groupadd -g ${HOST_GID} app \
 && useradd -u ${HOST_UID} -g app -m -s /bin/bash app

USER app:app

COPY --chmod=755 docker-entrypoint.sh /usr/local/bin/docker-entrypoint

ENTRYPOINT [ "docker-entrypoint" ]

CMD [ "symfony", "server:start", "--port=80", "--allow-http", "--no-tls", "--listen-ip=0.0.0.0"]

FROM base AS prod

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts

COPY . .

RUN composer dump-autoload --optimize

RUN composer dump-env prod

RUN mkdir -p /app/var/storage/

RUN mkdir -p /app/var/log/

RUN chown -R www-data:www-data var public

USER www-data

COPY ./.docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

COPY --chmod=755 docker-entrypoint.sh /usr/local/bin/docker-entrypoint

EXPOSE 9000

ENTRYPOINT [ "docker-entrypoint" ]

CMD ["php-fpm"]
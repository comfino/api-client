FROM php:8.2-cli-alpine

USER root

RUN apk add --no-cache git libzip-dev icu-dev gcc make autoconf libc-dev bash tzdata linux-headers

RUN docker-php-ext-configure intl && docker-php-ext-configure zip && docker-php-ext-install zip intl

# Set locale
ENV LANG pl_PL.UTF-8
ENV LC_ALL pl_PL.UTF-8

RUN cp /usr/share/zoneinfo/Europe/Warsaw /etc/localtime
RUN echo "Europe/Warsaw" > /etc/timezone

RUN apk del tzdata

RUN mkdir -p /usr/bin && wget -q -O /usr/bin/composer https://getcomposer.org/download/2.7.2/composer.phar && chmod +x /usr/bin/composer

RUN addgroup comfino
RUN adduser -S -u 1000 comfino -G comfino

USER comfino

FROM php:8.3-fpm
ENV PHP_VERSION=php8.3

WORKDIR /mnt/api

# Fix debconf warnings upon build
ARG DEBIAN_FRONTEND=noninteractive


# APT-GET installs
RUN apt-get update \
&& apt-get install -y --no-install-recommends apt-utils \
&& apt-get install -y net-tools \
&& apt-get install -y git \
&& apt-get install -y vim \
&& apt-get install -y curl \
&& apt-get install -y unzip \
&& apt-get install -y wget \
&& apt-get install -y libcurl4-gnutls-dev \
&& apt-get install -y libicu-dev \
&& apt-get install -y libmcrypt-dev \
&& apt-get install -y libpng-dev \
&& apt-get install -y libssl-dev \
&& apt-get install -y libxml2-dev \
&& apt-get install -y libsodium-dev \
&& apt-get install -y libzip-dev \
&& rm -rf /var/lib/apt/lists/*

# PHP Deps
RUN docker-php-ext-install zip
RUN docker-php-ext-install sockets
RUN docker-php-ext-install pdo_mysql

## Install and enable xDebug
RUN pecl install xdebug
#RUN docker-php-ext-enable xdebug

RUN pecl install -o -f redis \
&&  rm -rf /tmp/pear \
&&  docker-php-ext-enable redis

## Xdebug setup
RUN echo "zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20230831/xdebug.so" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.remote_handler=dbgp" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.discover_client_host=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.client_host=172.17.0.1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini # ip docker
RUN echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.log=/var/log/xdebug_remote.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.idekey=phpstorm" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN export XDEBUG_SESSION=phpstorm


# Prevent error in nginx error.log
RUN touch /var/log/xdebug_remote.log
RUN chmod 777 /var/log/xdebug_remote.log


# PHP Configuration
RUN echo "date.timezone=UTC" > /usr/local/etc/php/conf.d/timezone_sao_paulo.ini
RUN echo "upload_max_filesize=50M" >> /usr/local/etc/php/conf.d/max_size.ini

# Directories
# RUN mkdir -p /tmp/profiler && chmod 777 -R -f /tmp/profiler

RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

ENV COMPOSER_ALLOW_SUPERUSER=1


RUN ls -la &> /tmp/composer.out

#--ignore-platform-reqs \
#RUN composer install

EXPOSE 9000
EXPOSE 5601

# streams log
ENV LOG_STREAM="/tmp/stdout"
RUN mkfifo $LOG_STREAM && chmod 777 $LOG_STREAM


CMD ["php-fpm"]

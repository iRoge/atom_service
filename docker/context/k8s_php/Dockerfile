FROM php:7.4.16-fpm-buster

RUN apt-get update && apt-get install -y \
        curl \
        wget \
        git \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        zlib1g-dev \
        libxml2-dev \
        libzip-dev \
        libonig-dev \
        graphviz \
        libxslt1-dev

RUN docker-php-ext-configure gd \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install zip \
    && docker-php-source delete
# Locales
RUN apt-get update \
	&& apt-get install -y locales

RUN dpkg-reconfigure locales \
	&& locale-gen C.UTF-8 \
	&& /usr/sbin/update-locale LANG=C.UTF-8

RUN echo 'en_US.UTF-8 UTF-8' >> /etc/locale.gen \
	&& locale-gen

ENV LC_ALL C.UTF-8
ENV LANG en_US.UTF-8
ENV LANGUAGE en_US.UTF-8

RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl
RUN docker-php-ext-install xsl
RUN docker-php-ext-install soap

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN ln -s /usr/share/zoneinfo/Europe/Moscow localtime

ADD docker/context/k8s_php/php.ini /usr/local/etc/php/conf.d/40-custom.ini


COPY app/ /var/www/p5s/app/
COPY artisan /var/www/p5s/
COPY bootstrap/ /var/www/p5s/bootstrap/
COPY composer.json /var/www/p5s/
COPY config/ /var/www/p5s/config/
COPY database/ /var/www/p5s/database/
COPY _ide_helper_models.php /var/www/p5s/
COPY _ide_helper.php /var/www/p5s/
COPY phpunit.xml /var/www/p5s/
COPY public/ /var/www/p5s/public/
COPY README.md /var/www/p5s/
COPY requests/ /var/www/p5s/requests/
COPY resources/ /var/www/p5s/resources/
COPY routes/ /var/www/p5s/routes/
COPY storage/ /var/www/p5s/storage/
COPY tests/ /var/www/p5s/tests/

RUN chown -R www-data:www-data /var/www/p5s/
RUN cd /var/www/p5s/ && composer install

WORKDIR /var/www/p5s
USER www-data
CMD ["php-fpm"]

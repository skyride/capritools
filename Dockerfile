FROM php:fpm
MAINTAINER Rob Haswell <me@robhaswell.co.uk>

RUN apt-get -y update

RUN docker-php-ext-install pdo_mysql

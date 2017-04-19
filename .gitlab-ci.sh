#!/bin/bash

set -xe

# Update packages and install composer and PHP dependencies.
apt-get update -yqq
apt-get install git libcurl4-gnutls-dev libicu-dev libmcrypt-dev libvpx-dev libjpeg-dev libpng-dev libxpm-dev zlib1g-dev libfreetype6-dev libxml2-dev libexpat1-dev libbz2-dev libgmp3-dev libldap2-dev unixodbc-dev libpq-dev libsqlite3-dev libaspell-dev libsnmp-dev libpcre3-dev libtidy-dev -yqq
pecl install xdebug
echo "zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20151012/xdebug.so" > /usr/local/etc/php/conf.d/xdebug.ini

# Compile PHP, include these extensions.
docker-php-ext-install mbstring mcrypt pdo_mysql curl json intl gd xml zip bz2 opcache

# Install Composer and project dependencies.
EXPECTED_SIGNATURE=$(curl https://composer.github.io/installer.sig)
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '$EXPECTED_SIGNATURE') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php ./composer-setup.php --install-dir=/usr/local/bin --filename=composer
PATH=$PATH:/usr/local/bin/composer

# Git debug
echo $PATH
which git

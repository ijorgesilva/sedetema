FROM php:7.4-fpm-alpine
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    echo 'Installing PHP dependencies...' && \
    install-php-extensions gd mysqli SimpleXML curl xml mbstring intl json gd zip imap
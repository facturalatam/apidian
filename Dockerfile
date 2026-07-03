FROM rash07/php-fpm:8.2

RUN apt-get update && \
    apt-get -y install poppler-utils
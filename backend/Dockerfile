FROM php:8.2-cli
RUN apt update && apt install -y curl zip libzip-dev libssl-dev libbrotli-dev
RUN docker-php-ext-install zip && \
    docker-php-ext-install sockets
RUN pecl install swoole && \
    docker-php-ext-enable swoole
RUN apt clean && rm -rf /var/lib/apt/lists/*
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /var/www/code
CMD [ "tail", "-f", "/dev/null" ]

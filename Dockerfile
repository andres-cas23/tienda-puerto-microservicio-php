FROM php:8.3-cli

RUN docker-php-ext-install opcache

WORKDIR /app
COPY . .

EXPOSE 8080

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080}"]
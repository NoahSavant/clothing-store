# Dockerfile

# Use the official PHP 7.2 image
FROM php:7.2-fpm

# Set the working directory in the container
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]

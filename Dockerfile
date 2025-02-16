# Use the official PHP image with FPM and Alpine Linux
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    libxpm-dev \
    libxslt-dev \
    linux-headers

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install \
    pdo_mysql \
    zip \
    gd \
    mbstring \
    exif \
    pcntl \
    bcmath \
    xsl \
    opcache \
    sockets

# Install Redis extension using PECL
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set the working directory
WORKDIR /var/www/html

# Copy the Laravel application files
COPY . .

# Install Composer dependencies
RUN composer install --optimize-autoloader --no-dev

# Set permissions for Laravel storage and bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy Nginx configuration
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Copy Supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create directories for file uploads and set permissions
RUN mkdir -p /file-manager/uploads /file-manager/files \
    && chown -R www-data:www-data /file-manager/uploads /file-manager/files \
    && chmod -R 775 /file-manager/uploads /file-manager/files

# Increase PHP upload limits
RUN echo "upload_max_filesize = 20G" >> /usr/local/etc/php/conf.d/uploads.ini
RUN echo "post_max_size = 20G" >> /usr/local/etc/php/conf.d/uploads.ini
RUN echo "max_execution_time = 3600" >> /usr/local/etc/php/conf.d/uploads.ini
RUN echo "max_input_time = 3600" >> /usr/local/etc/php/conf.d/uploads.ini

# Expose port 9000 for PHP-FPM and 80 for Nginx
EXPOSE 9000 80

# Start Supervisor to manage Nginx and PHP-FPM
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
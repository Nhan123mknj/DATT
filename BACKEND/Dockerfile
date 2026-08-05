FROM php:8.3-fpm-alpine

# Cài đặt các extension cần thiết cho Laravel
RUN apk add --no-grad \
    nginx \
    supervisor \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    oniguruma-dev \
    libzip-dev

RUN docker-php-ext-install pdo pdo_mysql mbstring zip bcmath

# Cài Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy mã nguồn dự án vào container
COPY . .

# Cài đặt thư viện Laravel
RUN composer install --no-dev --optimize-autoloader

# Phân quyền cho storage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port
EXPOSE 80

# Chạy server
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=80
FROM php:8.3-fpm-alpine

# Cài đặt các thư viện cần thiết (Đã sửa --no-grad thành --no-cache)
RUN apk add --no-cache \
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

# Cài đặt các PHP extension
RUN docker-php-ext-install pdo pdo_mysql mbstring zip bcmath

# Copy Composer từ image chính thức
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy toàn bộ code backend vào container
COPY . .

# Cài đặt thư viện Laravel
RUN composer install --no-dev --optimize-autoloader

# Cấp quyền ghi file cho storage và cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=80
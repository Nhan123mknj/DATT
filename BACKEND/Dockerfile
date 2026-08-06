FROM php:8.3-fpm-alpine

# Cài đặt các thư viện hệ thống
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    oniguruma-dev \
    libzip-dev

# Cài đặt PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring zip bcmath gd xml

# Copy Composer từ image chính thức
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy toàn bộ code backend vào container
COPY . .

# Chạy composer install
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Cấp quyền ghi file cho storage và cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copy file cấu hình supervisor
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80

# Chạy migration và bật Supervisor để chạy cả Web + Queue cùng lúc
CMD php artisan migrate --force && supervisord -c /etc/supervisor/conf.d/supervisord.conf
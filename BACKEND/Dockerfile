FROM php:8.3-fpm-alpine

# Cài đặt các thư viện hệ thống (đã bổ sung libpng, libjpeg, freetype cho extension GD)
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

# Cài đặt PHP extension (đã thêm gd)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring zip bcmath gd xml

# Copy Composer từ image chính thức
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy toàn bộ code backend vào container
COPY . .

# Chạy composer install kết hợp ignore platform reqs để vượt qua mọi kiểm tra môi trường
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Cấp quyền ghi file cho storage và cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=80
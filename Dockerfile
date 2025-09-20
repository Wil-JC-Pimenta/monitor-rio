FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    nginx \
    supervisor \
    libicu-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    pdo_sqlite \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Create SQLite database
RUN touch database/database.sqlite

# Configure environment
RUN echo 'APP_NAME="Monitor Rio"' > .env \
    && echo 'APP_ENV=production' >> .env \
    && echo 'APP_KEY=' >> .env \
    && echo 'APP_DEBUG=false' >> .env \
    && echo 'APP_URL=http://localhost' >> .env \
    && echo 'DB_CONNECTION=sqlite' >> .env \
    && echo 'DB_DATABASE=/var/www/html/database/database.sqlite' >> .env \
    && echo 'CACHE_STORE=file' >> .env \
    && echo 'SESSION_DRIVER=file' >> .env \
    && echo 'QUEUE_CONNECTION=sync' >> .env \
    && php artisan key:generate --force \
    && php artisan migrate --force

# Configure Nginx
RUN echo 'server {' > /etc/nginx/sites-available/default \
    && echo '    listen 8080;' >> /etc/nginx/sites-available/default \
    && echo '    root /var/www/html/public;' >> /etc/nginx/sites-available/default \
    && echo '    index index.php;' >> /etc/nginx/sites-available/default \
    && echo '    server_name _;' >> /etc/nginx/sites-available/default \
    && echo '    location / {' >> /etc/nginx/sites-available/default \
    && echo '        try_files $uri $uri/ /index.php?$query_string;' >> /etc/nginx/sites-available/default \
    && echo '    }' >> /etc/nginx/sites-available/default \
    && echo '    location ~ \.php$ {' >> /etc/nginx/sites-available/default \
    && echo '        fastcgi_pass 127.0.0.1:9000;' >> /etc/nginx/sites-available/default \
    && echo '        fastcgi_index index.php;' >> /etc/nginx/sites-available/default \
    && echo '        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;' >> /etc/nginx/sites-available/default \
    && echo '        include fastcgi_params;' >> /etc/nginx/sites-available/default \
    && echo '    }' >> /etc/nginx/sites-available/default \
    && echo '}' >> /etc/nginx/sites-available/default

# Configure Supervisor
RUN echo '[supervisord]' > /etc/supervisor/conf.d/supervisord.conf \
    && echo 'nodaemon=true' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo '[program:nginx]' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'command=nginx -g "daemon off;"' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'autostart=true' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'autorestart=true' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo '[program:php-fpm]' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'command=php-fpm' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'autostart=true' >> /etc/supervisor/conf.d/supervisord.conf \
    && echo 'autorestart=true' >> /etc/supervisor/conf.d/supervisord.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

EXPOSE 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader --no-dev --no-interaction --ignore-platform-reqs

# Install npm dependencies and build assets
RUN npm install && npm run build

# Create necessary directories
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    storage/app/public/products \
    storage/app/public/articles \
    bootstrap/cache

# Copy product images to storage (flatten all subdirectories)
RUN find public/images/Foto\ Produk -type f \( -name "*.jpeg" -o -name "*.jpg" -o -name "*.png" \) -exec cp {} storage/app/public/products/ \; 2>/dev/null || true

# Copy article images to storage
RUN cp public/images/*.png storage/app/public/articles/ 2>/dev/null || true

# Create SQLite database
RUN touch /tmp/database.sqlite

# Create storage link
RUN ln -s /var/www/storage/app/public /var/www/public/storage || true

# Set permissions
RUN chmod -R 775 storage bootstrap/cache

# Expose port
EXPOSE 8080

# Create startup script
RUN echo '#!/bin/sh\n\
php artisan migrate:fresh --seed --force\n\
php artisan serve --host=0.0.0.0 --port=$PORT\n\
' > /var/www/start.sh && chmod +x /var/www/start.sh

# Start application
CMD ["/bin/sh", "/var/www/start.sh"]

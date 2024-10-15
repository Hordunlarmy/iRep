FROM php:8.3-cli

# Install system dependencies and required PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install \
    mysqli pdo pdo_mysql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www

# Copy the application files to the working directory
COPY . .

# Install Laravel dependencies
RUN composer install

# Set permissions for the Apache user
RUN chown -R www-data:www-data /var/www

# Expose the port for the Laravel application
EXPOSE 8000

# Run the Laravel application using artisan serve
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]


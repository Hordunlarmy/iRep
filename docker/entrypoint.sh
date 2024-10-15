#!/bin/bash

# Install Composer dependencies if not already installed
if [ ! -d "vendor" ]; then
	composer install
fi

# Reset and run Laravel migrations
php artisan migrate:reset --force
php artisan migrate --force

# Run Laravel seeders
php artisan db:seed --force

# Set permissions for the storage and bootstrap/cache directories
chown -R www-data:www-data storage bootstrap/cache

# Run the Laravel application using artisan serve
exec php artisan serve --host=0.0.0.0 --port=8000

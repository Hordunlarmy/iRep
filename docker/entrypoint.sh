#!/bin/bash

# Replace DB_HOST and DB_PORT in the .env file
sed -i "s/DB_HOST=.*/DB_HOST=database/" .env
sed -i "s/DB_PORT=.*/DB_PORT=3306/" .env

# Install Composer dependencies if not already installed
if [ ! -d "vendor" ]; then
	composer install
fi

# Run Laravel migrate and seed database
php artisan migrate:refresh --seed --force

# Index existing records in the database
php artisan meilisearch:index-existing

# Set permissions for the storage and bootstrap/cache directories
chown -R www-data:www-data storage bootstrap/cache

# Run the Laravel application using artisan serve
exec php artisan serve --host=0.0.0.0 --port=8000

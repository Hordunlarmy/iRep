#!/bin/bash

# Replace DB_HOST and DB_PORT in the .env file
sed -i "s/DB_HOST=.*/DB_HOST=database/" .env
sed -i "s/DB_PORT=.*/DB_PORT=3306/" .env

# Install Composer dependencies if not already installed
if [ ! -d "vendor" ]; then
	composer install
fi

# Run Laravel migrate and seed database
#php artisan migrate:refresh --seed --force

# Run Laravel migration and seeder gracefully
if ! php artisan migrate --force; then
	echo "Migration failed, proceeding without stopping the container..."
fi

if ! php artisan db:seed --force; then
	echo "Seeding failed, proceeding without stopping the container..."
fi

# Index existing records in the database
if ! php artisan search:index-existing; then
	echo "Indexing existing records failed, proceeding without stopping the container..."
fi

# Clear the cache, routes, config, and views
php artisan route:clear && php artisan config:clear && php artisan cache:clear && php artisan view:clear

# Set permissions for the storage and bootstrap/cache directories
chown -R www-data:www-data storage bootstrap/cache

# Start Supervisor to manage background processes
echo "Starting Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf

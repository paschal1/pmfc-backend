#!/bin/bash

# Wait for MySQL to be ready
echo "Waiting for MySQL..."
until nc -z db 3306; do
  sleep 1
done

# Laravel commands
php artisan config:clear
php artisan migrate --force
php artisan storage:link
apt-get update && apt-get install -y netcat


# Run Apache
apache2-foreground

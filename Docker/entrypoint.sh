#!/bin/sh
set -e

echo "Starting Laravel container..."

# Create .env if it does not exist
if [ ! -f ".env" ]; then
    echo "Creating .env file from .env.example"
    cp .env.example .env
else
    echo ".env file already exists"
fi

# Install Composer dependencies
if [ ! -f "vendor/autoload.php" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-progress --no-interaction
fi

# Generate application key if it does not exist
if ! grep -q "^APP_KEY=.\+" .env; then
    echo "Generating Laravel application key..."
    php artisan key:generate --force
fi

role=${CONTAINER_ROLE:-app}

if [ "$role" = "app" ]; then
    echo "Migrate database."
    php artisan migrate:fresh

   # Clear Laravel caches
    echo "Clearing Laravel caches..."
    php artisan optimize:clear

    echo "Seeding database..."
    php artisan db:seed --force

    echo "Starting Laravel server on port ${PORT:-8000}..."


    # Start Laravel HTTP server
    exec "$@"
    
elif [ "$role" = "queue" ]; then
    echo "Starting Laravel queue worker..."
    exec "$@"
fi


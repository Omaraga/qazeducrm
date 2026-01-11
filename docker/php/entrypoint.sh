#!/bin/sh
set -e

# Install dependencies if not present
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "Installing Composer dependencies..."
    cd /var/www/html && composer install --no-interaction --no-progress --prefer-dist
fi

# Initialize db.php config if not exists
if [ ! -f /var/www/html/config/db.php ]; then
    echo "Initializing db.php configuration..."
    cat > /var/www/html/config/db.php << 'DBCONF'
<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME'),
    'username' => getenv('DB_USER'),
    'password' => getenv('DB_PASS'),
    'charset' => 'utf8mb4',
];
DBCONF
fi

# Create runtime and assets directories if they don't exist
mkdir -p /var/www/html/runtime /var/www/html/web/assets
chown -R www-data:www-data /var/www/html/runtime /var/www/html/web/assets

# Wait for MySQL to be ready
echo "Waiting for MySQL..."
until mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" --skip-ssl -e "SELECT 1" "$DB_NAME" 2>/dev/null; do
    echo "MySQL is unavailable - sleeping..."
    sleep 2
done
echo "MySQL is ready!"

# Run migrations
echo "Running migrations..."
cd /var/www/html && php yii migrate --interactive=0

echo "Starting PHP-FPM..."
exec "$@"

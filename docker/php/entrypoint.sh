#!/bin/sh
set -e

# Install dependencies if not present
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "Installing Composer dependencies..."
    cd /var/www/html && composer install --no-interaction --no-progress --prefer-dist
fi

# db.php is already configured in repo to use environment variables

# Create runtime and assets directories if they don't exist
mkdir -p /var/www/html/runtime /var/www/html/web/assets
chown -R www-data:www-data /var/www/html/runtime /var/www/html/web/assets

# Wait for MySQL to be ready
echo "Waiting for MySQL..."
until mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" --skip-ssl --skip-ssl-verify-server-cert -e "SELECT 1" "$DB_NAME" 2>/dev/null; do
    echo "MySQL is unavailable - sleeping..."
    sleep 2
done
echo "MySQL is ready!"

# Run migrations (continue on errors for existing columns/tables)
echo "Running migrations..."
cd /var/www/html && php yii migrate --interactive=0 || echo "Some migrations failed (possibly due to existing schema), continuing..."

echo "Starting PHP-FPM..."
exec "$@"

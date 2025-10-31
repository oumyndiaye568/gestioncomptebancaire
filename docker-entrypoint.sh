#!/bin/sh

# Attendre que la base de données soit prête (avec timeout)
echo "Waiting for database to be ready..."
timeout=60
elapsed=0
while ! pg_isready -h $DB_HOST -p $DB_PORT -U $DB_USERNAME 2>/dev/null; do
  echo "Database is unavailable - sleeping"
  sleep 1
  elapsed=$((elapsed + 1))
  if [ $elapsed -ge $timeout ]; then
    echo "Database connection timeout reached. Continuing without database check..."
    break
  fi
done

echo "Database is up - executing migrations with --force"
php artisan migrate --force

echo "Creating admin user after migrations"
sleep 2
php artisan db:seed --class=AdminSeeder --force || echo "Admin seeder failed, continuing..."

echo "Installing/configuring Passport OAuth"
php artisan passport:install --force || echo "Passport install failed, continuing..."

echo "Running database seeders"
php artisan db:seed --force || echo "Seeding failed, continuing..."

echo "Clearing Laravel caches"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "Testing database connection"
php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'Database connection OK'; } catch(Exception \$e) { echo 'Database connection FAILED: ' . \$e->getMessage(); exit(1); }"

echo "Generating Swagger documentation"
php artisan l5-swagger:generate || echo "Swagger generation failed, continuing..."

echo "Caching configuration for production"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting Laravel application..."
exec "$@"
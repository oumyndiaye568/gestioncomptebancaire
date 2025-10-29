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

echo "Database is up - executing migrations"
php artisan migrate --force || echo "Migration failed, continuing..."

echo "Running database seeders"
php artisan db:seed --force || echo "Seeding failed, continuing..."

echo "Creating admin user"
php artisan tinker --execute="App\Models\Admin::firstOrCreate(['email' => 'admin@test.com'], ['nom' => 'Admin Test', 'password' => \Illuminate\Support\Facades\Hash::make('password')]);" || echo "Admin creation failed, continuing..."

echo "Updating client passwords"
php artisan tinker --execute="App\Models\Client::all()->each(function(\$client) { \$client->update(['password' => \Illuminate\Support\Facades\Hash::make('password')]); });" || echo "Client password update failed, continuing..."

echo "Clearing Laravel caches"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "Generating Swagger documentation"
php artisan l5-swagger:generate || echo "Swagger generation failed, continuing..."

echo "Caching configuration for production"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting Laravel application..."
exec "$@"
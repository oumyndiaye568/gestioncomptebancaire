#!/bin/sh

# Attendre que la base de données soit prête
echo "Waiting for database to be ready..."
while ! pg_isready -h $DB_HOST -p $DB_PORT -U $DB_USERNAME; do
  echo "Database is unavailable - sleeping"
  sleep 1
done

echo "Database is up - executing migrations"
php artisan migrate --force

echo "Running database seeders"
php artisan db:seed --force

echo "Creating admin user"
php artisan tinker --execute="App\Models\Admin::firstOrCreate(['email' => 'admin@test.com'], ['nom' => 'Admin Test', 'password' => \Illuminate\Support\Facades\Hash::make('password')]);"

echo "Updating client passwords"
php artisan tinker --execute="App\Models\Client::all()->each(function(\$client) { \$client->update(['password' => \Illuminate\Support\Facades\Hash::make('password')]); });"

echo "Generating Swagger documentation"
php artisan l5-swagger:generate

echo "Starting Laravel application..."
exec "$@"
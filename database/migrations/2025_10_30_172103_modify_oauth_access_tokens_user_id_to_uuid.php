<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('oauth_access_tokens')) {
            // Vérifier le type actuel de la colonne
            $column = DB::select("SELECT data_type FROM information_schema.columns WHERE table_name = 'oauth_access_tokens' AND column_name = 'user_id'");
            if (!empty($column) && $column[0]->data_type !== 'uuid') {
                // Utiliser du SQL brut pour PostgreSQL car Laravel ne gère pas bien la conversion
                // D'abord, supprimer les tokens existants qui pourraient causer des conflits
                DB::statement('DELETE FROM oauth_access_tokens WHERE user_id IS NOT NULL');
                // Ensuite, convertir la colonne en UUID
                DB::statement('ALTER TABLE oauth_access_tokens ALTER COLUMN user_id TYPE UUID USING user_id::text::uuid');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            // Supprimer la colonne UUID
            $table->dropColumn('user_id');

            // Recréer avec le type original
            $table->unsignedBigInteger('user_id')->nullable()->index()->after('id');
        });
    }
};

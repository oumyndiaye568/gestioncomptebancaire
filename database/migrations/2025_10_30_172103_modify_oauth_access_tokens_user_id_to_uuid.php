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
        // Utiliser du SQL brut pour PostgreSQL car Laravel ne gère pas bien la conversion
        DB::statement('ALTER TABLE oauth_access_tokens ALTER COLUMN user_id TYPE UUID USING user_id::text::uuid');
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

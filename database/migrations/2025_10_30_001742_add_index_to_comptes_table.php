<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('comptes')) {
            Schema::table('comptes', function (Blueprint $table) {
                // Index pour optimiser les requêtes de tri et filtrage
                if (!$table->hasIndex('idx_comptes_type_etat')) {
                    $table->index(['type_compte', 'etat_compte'], 'idx_comptes_type_etat');
                }
                if (!$table->hasIndex('idx_comptes_solde')) {
                    $table->index('solde', 'idx_comptes_solde');
                }
                if (!$table->hasIndex('idx_comptes_created_at')) {
                    $table->index('created_at', 'idx_comptes_created_at');
                }
                if (!$table->hasIndex('idx_comptes_client_id')) {
                    $table->index('client_id', 'idx_comptes_client_id');
                }
                if (!$table->hasIndex('idx_comptes_numero')) {
                    $table->index('numero_compte', 'idx_comptes_numero');
                }
                if (!$table->hasIndex('idx_comptes_archived')) {
                    $table->index('is_archived', 'idx_comptes_archived');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            // Supprimer les index
            $table->dropIndex('idx_comptes_type_etat');
            $table->dropIndex('idx_comptes_solde');
            $table->dropIndex('idx_comptes_created_at');
            $table->dropIndex('idx_comptes_client_id');
            $table->dropIndex('idx_comptes_numero');
            $table->dropIndex('idx_comptes_archived');
        });
    }
};

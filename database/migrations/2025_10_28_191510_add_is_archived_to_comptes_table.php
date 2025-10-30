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
        if (Schema::hasTable('comptes') && !Schema::hasColumn('comptes', 'is_archived')) {
            Schema::table('comptes', function (Blueprint $table) {
                $table->boolean('is_archived')->default(false)->after('motif_blocage');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->dropColumn('is_archived');
        });
    }
};

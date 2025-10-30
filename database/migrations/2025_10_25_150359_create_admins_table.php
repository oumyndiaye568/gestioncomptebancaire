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
        if (!Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('nom');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('role')->default('admin');
                $table->timestamps();
            });

            // Ajouter les colonnes nécessaires pour Sanctum
            Schema::table('admins', function (Blueprint $table) {
                $table->rememberToken();
            });
        } elseif (!Schema::hasColumn('admins', 'role')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->string('role')->default('admin');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};

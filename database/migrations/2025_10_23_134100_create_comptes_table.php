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
        Schema::create('comptes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('titulaire');
            $table->string('numero_compte');
            $table->enum('type_compte',['cheque','epargne']);
            $table->enum('etat_compte',['actif','inactif','bloque']);
            $table->decimal('solde', 15, 2)->default(0); // solde du compte
            $table->string('motif_blocage')->nullable();  // raison du blocage si existante
            $table->uuid('client_id'); // clé étrangère vers clients
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};

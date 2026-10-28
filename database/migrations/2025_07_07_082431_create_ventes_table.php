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
        Schema::create('ventes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');// Empêche la suppression si ventes liées
            $table->string('code_recu')->unique()->nullable();
            $table->integer('montant_total');
            $table->integer('remise')->nullable();
            $table->enum('statut', ['valide', 'annulee'])->default('valide');
            $table->date('date_vente');
            $table->string('mode_paiement');
            $table->string('pdf_recu')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventes');
    }
};

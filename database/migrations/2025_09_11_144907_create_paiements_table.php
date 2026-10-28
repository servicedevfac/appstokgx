<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vente_id')->constrained()->onDelete('cascade'); // Lien vers la vente
            $table->decimal('montant', 15, 2); // Montant payé
            $table->string('mode_paiement')->nullable(); // Espèces, Mobile Money, Carte, etc.
            $table->dateTime('date_paiement')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};

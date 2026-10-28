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
        Schema::create('depenses', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->string('categorie'); // Loyer, Électricité & Eau, Transport, Salaires, Fournitures, etc.
            $table->decimal('montant', 15, 2);
            $table->date('date_depense');
            $table->string('mode_paiement')->default('Espèces');
            $table->string('beneficiaire')->nullable();
            $table->string('justificatif')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['date_depense', 'categorie']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depenses');
    }
};

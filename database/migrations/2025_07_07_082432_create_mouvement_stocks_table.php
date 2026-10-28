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
        Schema::create('mouvement_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produit_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict'); // Empêche la suppression si ventes liées
            $table->foreignId('vente_id')->nullable()->constrained('ventes')->nullOnDelete();
            $table->integer('quantite');
            $table->enum('type_mouvement', ['entree', 'sortie']);
            $table->date('date_mouvement');
            $table->string('motif')->nullable();
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('mouvement_stocks');
    }
};

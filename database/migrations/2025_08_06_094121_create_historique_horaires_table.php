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
        Schema::create('historique_horaires', function (Blueprint $table) {
    $table->id();
    $table->string('jour_semaine');
    $table->time('ancienne_ouverture')->nullable();
    $table->time('ancienne_fermeture')->nullable();
    $table->time('nouvelle_ouverture')->nullable();
    $table->time('nouvelle_fermeture')->nullable();
    $table->unsignedBigInteger('id_utilisateur');
    $table->timestamps();
    $table->foreign('id_utilisateur')->references('id')->on('users')->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historique_horaires');
    }
};

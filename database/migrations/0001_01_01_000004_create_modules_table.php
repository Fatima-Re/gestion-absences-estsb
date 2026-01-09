<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ex: GI201, GI202
            $table->string('name'); // Nom du module
            $table->text('description')->nullable();
            $table->integer('volume_horaire'); // Volume horaire total
            $table->integer('volume_cm')->default(0); // Cours magistraux
            $table->integer('volume_td')->default(0); // Travaux dirigés
            $table->integer('volume_tp')->default(0); // Travaux pratiques
            $table->integer('semester'); // Semestre (1 ou 2)
            $table->enum('type', ['obligatoire', 'optionnel'])->default('obligatoire');
            $table->integer('coefficient')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
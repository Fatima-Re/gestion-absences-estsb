<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('student_code')->unique(); // Ex: SB2024001
            $table->string('cin')->unique()->nullable(); // Carte d'identité
            $table->string('cne')->unique()->nullable(); // Pour les étudiants marocains
            $table->date('date_of_birth')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('filiere'); // Ex: Génie Informatique
            $table->string('niveau'); // Ex: DUT 2ème année
            $table->integer('academic_year'); // Ex: 2025
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
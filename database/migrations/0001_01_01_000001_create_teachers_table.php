<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('teacher_code')->unique(); // Ex: ENS001
            $table->string('cin')->unique()->nullable();
            $table->string('specialty')->nullable(); // Ex: Informatique, Mathématiques
            $table->string('grade')->nullable(); // Ex: Professeur, Maître de Conférences
            $table->string('phone')->nullable();
            $table->string('office')->nullable(); // Bureau
            $table->date('hire_date')->nullable(); // Date d'embauche
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
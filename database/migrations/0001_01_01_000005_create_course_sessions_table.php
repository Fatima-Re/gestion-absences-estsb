<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->string('title'); // Titre de la séance
            $table->text('description')->nullable();
            $table->datetime('start_time'); // Date et heure de début
            $table->datetime('end_time'); // Date et heure de fin
            $table->integer('duration'); // Durée en minutes
            $table->string('classroom'); // Salle de cours
            $table->enum('session_type', ['cm', 'td', 'tp', 'autre'])->default('cm');
            $table->enum('status', ['planned', 'completed', 'cancelled'])->default('planned');
            $table->boolean('attendance_taken')->default(false);
            $table->timestamps();
            
            // Index pour optimisation
            $table->index(['start_time', 'end_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_sessions');
    }
};
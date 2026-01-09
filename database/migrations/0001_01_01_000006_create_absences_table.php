<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('session_id')->constrained('course_sessions')->onDelete('cascade');
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('absent');
            $table->integer('late_minutes')->nullable(); // Si retard
            $table->text('comments')->nullable(); // Commentaires de l'enseignant
            $table->enum('justification_status', ['none', 'pending', 'approved', 'rejected'])->default('none');
            $table->text('justification_reason')->nullable(); // Raison de l'absence
            $table->timestamp('justified_at')->nullable();
            $table->foreignId('justified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Une absence unique par étudiant par séance
            $table->unique(['student_id', 'session_id']);
            
            // Index pour les recherches fréquentes
            $table->index(['justification_status', 'created_at']);
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
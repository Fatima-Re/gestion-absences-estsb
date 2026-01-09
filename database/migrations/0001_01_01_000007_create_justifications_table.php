<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('justifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('absence_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['medical', 'official', 'family', 'personal', 'transport', 'other']);
            $table->text('reason'); // Détail de la justification
            $table->string('file_path')->nullable(); // Chemin du fichier justificatif
            $table->string('file_name')->nullable(); // Nom original du fichier
            $table->integer('file_size')->nullable(); // Taille en Ko
            $table->date('start_date'); // Période couverte
            $table->date('end_date');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_comments')->nullable(); // Commentaires de l'admin
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            // Index
            $table->index(['student_id', 'status']);
            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('justifications');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['responsable', 'intervenant', 'suppleant'])->default('intervenant');
            $table->integer('assigned_hours')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Un enseignant ne peut être assigné qu'une fois à un module
            $table->unique(['module_id', 'teacher_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_teacher');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_module', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->integer('academic_year');
            $table->enum('semester', [1, 2]);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Un module ne peut être assigné qu'une fois à un groupe par année/semestre
            $table->unique(['group_id', 'module_id', 'academic_year', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_module');
    }
};
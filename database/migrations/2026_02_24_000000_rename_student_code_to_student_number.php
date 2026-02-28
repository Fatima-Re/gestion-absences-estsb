<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // rename column and keep unique constraint
            if (Schema::hasColumn('students', 'student_code')) {
                $table->renameColumn('student_code', 'student_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'student_number')) {
                $table->renameColumn('student_number', 'student_code');
            }
        });
    }
};

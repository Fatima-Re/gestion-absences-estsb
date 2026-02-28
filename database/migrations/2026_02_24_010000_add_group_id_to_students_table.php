<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'group_id')) {
                $table->foreignId('group_id')->nullable()->constrained()->onDelete('set null')->after('filiere');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'group_id')) {
                $table->dropConstrainedForeignId('group_id');
            }
        });
    }
};

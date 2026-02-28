<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            // Drop the departement column we added previously (if it exists)
            if (Schema::hasColumn('teachers', 'departement')) {
                $table->dropColumn('departement');
            }
        });
        
        Schema::table('teachers', function (Blueprint $table) {
            // Rename specialty to departement
            $table->renameColumn('specialty', 'departement');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            // Rename back to specialty
            $table->renameColumn('departement', 'specialty');
        });
    }
};

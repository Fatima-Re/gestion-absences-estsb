<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            DB::table('course_sessions')
                ->where('status', 'planned')
                ->update(['status' => 'scheduled']);

            return;
        }

        DB::statement("
            ALTER TABLE course_sessions
            MODIFY COLUMN status ENUM('planned','scheduled','completed','cancelled')
            NOT NULL DEFAULT 'scheduled'
        ");

        DB::table('course_sessions')
            ->where('status', 'planned')
            ->update(['status' => 'scheduled']);
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            DB::table('course_sessions')
                ->where('status', 'scheduled')
                ->update(['status' => 'planned']);

            return;
        }

        DB::table('course_sessions')
            ->where('status', 'scheduled')
            ->update(['status' => 'planned']);

        DB::statement("
            ALTER TABLE course_sessions
            MODIFY COLUMN status ENUM('planned','completed','cancelled')
            NOT NULL DEFAULT 'planned'
        ");
    }
};

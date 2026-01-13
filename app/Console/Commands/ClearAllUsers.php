<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearAllUsers extends Command
{
    protected $signature = 'users:clear {--all : Clear all related data (absences, sessions, etc.)}';
    protected $description = 'Remove all users and optionally all related data from the database';

    public function handle()
    {
        $clearAll = $this->option('all');
        
        $this->warn('⚠️  WARNING: This will delete all user data!');
        
        if ($clearAll) {
            $this->warn('⚠️  This will also delete ALL related data (absences, sessions, groups, modules, etc.)!');
        }
        
        if (!$this->confirm('Are you sure you want to proceed?')) {
            $this->info('Operation cancelled.');
            return 0;
        }
        
        $this->info('Clearing user data...');
        
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        try {
            if ($clearAll) {
                // Clear all related tables first
                $this->line('Clearing related data...');
                
                $tables = [
                    'notifications',
                    'justifications',
                    'absences',
                    'attendance_records',
                    'course_sessions',
                    'group_student',
                    'module_teacher',
                    'group_module',
                    'sessions',
                    'modules',
                    'groups',
                    'students',
                    'teachers',
                ];
                
                foreach ($tables as $table) {
                    try {
                        DB::table($table)->truncate();
                        $this->line("  ✓ Cleared: $table");
                    } catch (\Exception $e) {
                        $this->warn("  ⚠ Could not clear: $table - " . $e->getMessage());
                    }
                }
            }
            
            // Clear users table
            $userCount = User::count();
            User::truncate();
            $this->info("✓ Deleted $userCount user(s)");
            
            // Clear password reset tokens if table exists
            try {
                DB::table('password_reset_tokens')->truncate();
                $this->line('✓ Cleared password reset tokens');
            } catch (\Exception $e) {
                // Table might not exist, that's okay
            }
            
            $this->info("\n✅ All user data cleared successfully!");
            $this->line("\nYou can now create new users using:");
            $this->line("  php artisan user:create-admin admin@example.com \"Admin Name\"");
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        
        return 0;
    }
}

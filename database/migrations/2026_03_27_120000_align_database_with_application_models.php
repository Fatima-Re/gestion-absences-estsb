<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('justifications');
        Schema::dropIfExists('absences');
        Schema::dropIfExists('attendance_records');

        $this->upgradeCourseSessionsTable();
        $this->upgradeGroupsTable();
        $this->upgradeModulesTable();
        $this->upgradeNotificationsTable();
        $this->upgradeSettingsTable();

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('course_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('status', 32);
            $table->text('comments')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('teachers')->nullOnDelete();
            $table->timestamp('recorded_at')->nullable();
            $table->boolean('is_late')->default(false);
            $table->unsignedSmallInteger('late_minutes')->nullable();
            $table->timestamps();

            $table->unique(['session_id', 'student_id']);
            $table->index(['student_id', 'status']);
        });

        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('course_sessions')->cascadeOnDelete();
            $table->foreignId('attendance_record_id')->nullable()->constrained('attendance_records')->nullOnDelete();
            $table->string('status', 32)->default('unjustified');
            $table->unsignedBigInteger('justification_id')->nullable();
            $table->string('absence_type', 32)->default('absence');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'session_id']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('justifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('absence_id')->nullable()->constrained('absences')->cascadeOnDelete();
            $table->foreignId('attendance_record_id')->nullable()->constrained('attendance_records')->nullOnDelete();
            $table->string('type', 32);
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->string('file_mime')->nullable();
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 32)->default('pending');
            $table->timestamp('validation_date')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
        });

        Schema::table('absences', function (Blueprint $table) {
            $table->foreign('justification_id')->references('id')->on('justifications')->nullOnDelete();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('justifications');
        Schema::dropIfExists('absences');
        Schema::dropIfExists('attendance_records');

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('course_sessions')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->integer('total_students');
            $table->integer('present_count');
            $table->integer('absent_count');
            $table->integer('late_count');
            $table->decimal('attendance_rate', 5, 2);
            $table->boolean('is_finalized')->default(false);
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            $table->index(['session_id', 'is_finalized']);
            $table->index(['teacher_id', 'created_at']);
        });

        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('course_sessions')->cascadeOnDelete();
            $table->string('status', 32)->default('absent');
            $table->integer('late_minutes')->nullable();
            $table->text('comments')->nullable();
            $table->string('justification_status', 32)->default('none');
            $table->text('justification_reason')->nullable();
            $table->timestamp('justified_at')->nullable();
            $table->foreignId('justified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['student_id', 'session_id']);
        });

        Schema::create('justifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('absence_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32);
            $table->text('reason');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->integer('file_size')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 32)->default('pending');
            $table->text('admin_comments')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        $this->downgradeCourseSessionsTable();
        $this->downgradeGroupsTable();
        $this->downgradeModulesTable();
        $this->downgradeNotificationsTable();
        $this->downgradeSettingsTable();

        Schema::enableForeignKeyConstraints();
    }

    private function upgradeCourseSessionsTable(): void
    {
        if (!Schema::hasTable('course_sessions')) {
            return;
        }

        Schema::table('course_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('course_sessions', 'classroom') && !Schema::hasColumn('course_sessions', 'room')) {
                $table->renameColumn('classroom', 'room');
            }
        });

        Schema::table('course_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('course_sessions', 'date')) {
                $table->date('date')->nullable()->after('teacher_id');
            }
            if (!Schema::hasColumn('course_sessions', 'topic')) {
                $table->string('topic')->nullable()->after('description');
            }
            if (!Schema::hasColumn('course_sessions', 'is_cancelled')) {
                $table->boolean('is_cancelled')->default(false);
            }
            if (!Schema::hasColumn('course_sessions', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable();
            }
        });

        foreach (DB::table('course_sessions')->cursor() as $row) {
            $updates = [];
            if (!empty($row->start_time)) {
                try {
                    $updates['date'] = \Carbon\Carbon::parse($row->start_time)->format('Y-m-d');
                } catch (\Throwable) {
                }
            }
            if (Schema::hasColumn('course_sessions', 'title') && empty($row->topic ?? null) && !empty($row->title ?? null)) {
                $updates['topic'] = $row->title;
            }
            if (!empty($updates)) {
                DB::table('course_sessions')->where('id', $row->id)->update($updates);
            }
        }

        DB::table('course_sessions')->where('status', 'planned')->update(['status' => 'scheduled']);

        Schema::table('course_sessions', function (Blueprint $table) {
            foreach (['title', 'duration', 'session_type', 'attendance_taken'] as $col) {
                if (Schema::hasColumn('course_sessions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    private function downgradeCourseSessionsTable(): void
    {
        if (!Schema::hasTable('course_sessions')) {
            return;
        }

        Schema::table('course_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('course_sessions', 'title')) {
                $table->string('title')->default('Séance')->after('teacher_id');
            }
            if (!Schema::hasColumn('course_sessions', 'duration')) {
                $table->integer('duration')->default(60)->after('end_time');
            }
            if (!Schema::hasColumn('course_sessions', 'session_type')) {
                $table->string('session_type', 16)->default('cm')->after('duration');
            }
            if (!Schema::hasColumn('course_sessions', 'attendance_taken')) {
                $table->boolean('attendance_taken')->default(false);
            }
        });

        Schema::table('course_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('course_sessions', 'room') && !Schema::hasColumn('course_sessions', 'classroom')) {
                $table->renameColumn('room', 'classroom');
            }
        });

        DB::table('course_sessions')->where('status', 'scheduled')->update(['status' => 'planned']);

        Schema::table('course_sessions', function (Blueprint $table) {
            foreach (['date', 'topic', 'is_cancelled', 'cancellation_reason'] as $col) {
                if (Schema::hasColumn('course_sessions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    private function upgradeGroupsTable(): void
    {
        if (!Schema::hasTable('groups')) {
            return;
        }

        Schema::table('groups', function (Blueprint $table) {
            if (!Schema::hasColumn('groups', 'code')) {
                $table->string('code')->nullable()->after('name');
            }
            if (!Schema::hasColumn('groups', 'level')) {
                $table->string('level')->nullable()->after('code');
            }
            if (!Schema::hasColumn('groups', 'specialty')) {
                $table->string('specialty')->nullable()->after('level');
            }
            if (!Schema::hasColumn('groups', 'semester')) {
                $table->unsignedTinyInteger('semester')->nullable()->after('academic_year');
            }
        });

        foreach (DB::table('groups')->cursor() as $row) {
            $updates = [];
            if (Schema::hasColumn('groups', 'filiere') && empty($row->specialty ?? null)) {
                $updates['specialty'] = $row->filiere;
            }
            if (Schema::hasColumn('groups', 'niveau') && empty($row->level ?? null)) {
                $updates['level'] = $row->niveau;
            }
            if (empty($row->code ?? null)) {
                $updates['code'] = 'GRP-' . $row->id;
            }
            if (($row->semester ?? null) === null) {
                $updates['semester'] = 1;
            }
            if (!empty($updates)) {
                DB::table('groups')->where('id', $row->id)->update($updates);
            }
        }

        Schema::table('groups', function (Blueprint $table) {
            if (Schema::hasColumn('groups', 'filiere')) {
                $table->dropColumn('filiere');
            }
            if (Schema::hasColumn('groups', 'niveau')) {
                $table->dropColumn('niveau');
            }
        });
    }

    private function downgradeGroupsTable(): void
    {
        if (!Schema::hasTable('groups')) {
            return;
        }

        Schema::table('groups', function (Blueprint $table) {
            if (!Schema::hasColumn('groups', 'filiere')) {
                $table->string('filiere')->default('')->after('name');
            }
            if (!Schema::hasColumn('groups', 'niveau')) {
                $table->string('niveau')->default('')->after('filiere');
            }
        });

        foreach (DB::table('groups')->cursor() as $row) {
            DB::table('groups')->where('id', $row->id)->update([
                'filiere' => $row->specialty ?? '',
                'niveau' => $row->level ?? '',
            ]);
        }

        Schema::table('groups', function (Blueprint $table) {
            foreach (['code', 'level', 'specialty', 'semester'] as $col) {
                if (Schema::hasColumn('groups', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    private function upgradeModulesTable(): void
    {
        if (!Schema::hasTable('modules')) {
            return;
        }

        Schema::table('modules', function (Blueprint $table) {
            if (!Schema::hasColumn('modules', 'credits')) {
                $table->unsignedTinyInteger('credits')->default(1)->after('description');
            }
            if (!Schema::hasColumn('modules', 'hours')) {
                $table->unsignedSmallInteger('hours')->default(30)->after('credits');
            }
            if (!Schema::hasColumn('modules', 'academic_year')) {
                $table->string('academic_year', 32)->nullable()->after('semester');
            }
        });

        $defaultYear = (string) now()->year . '-' . (string) (now()->year + 1);

        foreach (DB::table('modules')->cursor() as $row) {
            DB::table('modules')->where('id', $row->id)->update([
                'credits' => max(1, (int) ($row->coefficient ?? 1)),
                'hours' => (int) ($row->volume_horaire ?? 30),
                'academic_year' => $defaultYear,
            ]);
        }

        Schema::table('modules', function (Blueprint $table) {
            foreach (['volume_horaire', 'volume_cm', 'volume_td', 'volume_tp', 'type', 'coefficient'] as $col) {
                if (Schema::hasColumn('modules', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    private function downgradeModulesTable(): void
    {
        if (!Schema::hasTable('modules')) {
            return;
        }

        Schema::table('modules', function (Blueprint $table) {
            if (!Schema::hasColumn('modules', 'volume_horaire')) {
                $table->integer('volume_horaire')->default(30)->after('description');
            }
            if (!Schema::hasColumn('modules', 'volume_cm')) {
                $table->integer('volume_cm')->default(0);
            }
            if (!Schema::hasColumn('modules', 'volume_td')) {
                $table->integer('volume_td')->default(0);
            }
            if (!Schema::hasColumn('modules', 'volume_tp')) {
                $table->integer('volume_tp')->default(0);
            }
            if (!Schema::hasColumn('modules', 'type')) {
                $table->string('type', 32)->default('obligatoire');
            }
            if (!Schema::hasColumn('modules', 'coefficient')) {
                $table->integer('coefficient')->default(1);
            }
        });

        foreach (DB::table('modules')->cursor() as $row) {
            DB::table('modules')->where('id', $row->id)->update([
                'volume_horaire' => $row->hours ?? 30,
                'coefficient' => $row->credits ?? 1,
            ]);
        }

        Schema::table('modules', function (Blueprint $table) {
            foreach (['credits', 'hours', 'academic_year'] as $col) {
                if (Schema::hasColumn('modules', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    private function upgradeNotificationsTable(): void
    {
        if (!Schema::hasTable('notifications')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'title')) {
                $table->string('title')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('notifications', 'related_model')) {
                $table->string('related_model')->nullable()->after('data');
            }
            if (!Schema::hasColumn('notifications', 'related_id')) {
                $table->unsignedBigInteger('related_id')->nullable()->after('related_model');
            }
            if (!Schema::hasColumn('notifications', 'action_url')) {
                $table->string('action_url')->nullable()->after('related_id');
            }
            if (!Schema::hasColumn('notifications', 'priority')) {
                $table->unsignedTinyInteger('priority')->default(2)->after('action_url');
            }
        });
    }

    private function downgradeNotificationsTable(): void
    {
        if (!Schema::hasTable('notifications')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            foreach (['title', 'related_model', 'related_id', 'action_url', 'priority'] as $col) {
                if (Schema::hasColumn('notifications', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    private function upgradeSettingsTable(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'is_editable')) {
                $table->boolean('is_editable')->default(true)->after('group');
            }
            if (!Schema::hasColumn('settings', 'options')) {
                $table->json('options')->nullable()->after('description');
            }
        });
    }

    private function downgradeSettingsTable(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            foreach (['is_editable', 'options'] as $col) {
                if (Schema::hasColumn('settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

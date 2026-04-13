<?php

namespace Database\Seeders;

use App\Models\Absence;
use App\Models\AttendanceRecord;
use App\Models\CourseSession;
use App\Models\Group;
use App\Models\Justification;
use App\Models\Module;
use App\Models\Notification;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Additive demo data for all application tables. Safe to run multiple times:
 * uses unique emails/codes and skips rows that already exist.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::transaction(function () use ($now) {
            $this->seedLegacyBootstrapUsersIfMissing();

            $admin = User::firstOrCreate(
                ['email' => 'demo.admin@demo.local'],
                [
                    'name' => 'Administrateur Démo',
                    'password' => Hash::make('demo123'),
                    'role' => User::ROLE_ADMIN,
                    'is_active' => true,
                ]
            );

            $teacherUsers = [
                ['email' => 'demo.enseignant1@demo.local', 'name' => 'Enseignant Démo Un'],
                ['email' => 'demo.enseignant2@demo.local', 'name' => 'Enseignant Démo Deux'],
            ];

            $teachers = [];
            foreach ($teacherUsers as $i => $tu) {
                $user = User::firstOrCreate(
                    ['email' => $tu['email']],
                    [
                        'name' => $tu['name'],
                        'password' => Hash::make('demo123'),
                        'role' => User::ROLE_TEACHER,
                        'is_active' => true,
                    ]
                );

                $code = 'DEMO-T0'.($i + 1);
                $teachers[] = Teacher::firstOrCreate(
                    ['teacher_code' => $code],
                    [
                        'user_id' => $user->id,
                        'departement' => $i === 0 ? 'Informatique' : 'Mathématiques',
                    ]
                );
            }

            $studentSpecs = [
                ['email' => 'demo.etudiant1@demo.local', 'name' => 'Étudiant Démo Alpha', 'num' => 'DEMO-2025-001'],
                ['email' => 'demo.etudiant2@demo.local', 'name' => 'Étudiant Démo Beta', 'num' => 'DEMO-2025-002'],
                ['email' => 'demo.etudiant3@demo.local', 'name' => 'Étudiant Démo Gamma', 'num' => 'DEMO-2025-003'],
                ['email' => 'demo.etudiant4@demo.local', 'name' => 'Étudiant Démo Delta', 'num' => 'DEMO-2025-004'],
            ];

            $group = Group::firstOrCreate(
                ['code' => 'DEMO-GI-2025'],
                [
                    'name' => 'Groupe Démo GI 2025',
                    'level' => '1ère année',
                    'specialty' => 'Génie Informatique',
                    'max_students' => 40,
                    'academic_year' => 2025,
                    'semester' => 1,
                    'is_active' => true,
                    'description' => 'Groupe créé automatiquement pour la démonstration.',
                ]
            );

            $students = [];
            foreach ($studentSpecs as $ss) {
                $user = User::firstOrCreate(
                    ['email' => $ss['email']],
                    [
                        'name' => $ss['name'],
                        'password' => Hash::make('demo123'),
                        'role' => User::ROLE_STUDENT,
                        'is_active' => true,
                    ]
                );

                $students[] = Student::firstOrCreate(
                    ['student_number' => $ss['num']],
                    [
                        'user_id' => $user->id,
                        'filiere' => 'Génie Informatique',
                        'niveau' => '1ère année',
                        'academic_year' => 2025,
                        'group_id' => $group->id,
                    ]
                );
            }

            foreach ($students as $student) {
                if ((int) $student->group_id !== (int) $group->id) {
                    $student->update(['group_id' => $group->id]);
                }

                $exists = DB::table('group_student')
                    ->where('group_id', $group->id)
                    ->where('student_id', $student->id)
                    ->where('is_active', true)
                    ->exists();

                if (! $exists) {
                    DB::table('group_student')->insert([
                        'group_id' => $group->id,
                        'student_id' => $student->id,
                        'joined_at' => $now->toDateString(),
                        'left_at' => null,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            $moduleDefs = [
                [
                    'code' => 'DEMO-PROG101',
                    'name' => 'Programmation — Démo',
                    'description' => 'Module de démonstration (algorithmique et structures).',
                    'credits' => 3,
                    'hours' => 60,
                    'semester' => 1,
                    'academic_year' => '2025-2026',
                ],
                [
                    'code' => 'DEMO-MATH102',
                    'name' => 'Mathématiques — Démo',
                    'description' => 'Module de démonstration (analyse).',
                    'credits' => 3,
                    'hours' => 45,
                    'semester' => 1,
                    'academic_year' => '2025-2026',
                ],
            ];

            $modules = [];
            foreach ($moduleDefs as $md) {
                $modules[] = Module::firstOrCreate(
                    ['code' => $md['code']],
                    array_merge($md, ['is_active' => true])
                );
            }

            foreach ($modules as $idx => $module) {
                $tid = $teachers[$idx % count($teachers)]->id;
                DB::table('module_teacher')->updateOrInsert(
                    [
                        'module_id' => $module->id,
                        'teacher_id' => $tid,
                        'role' => 'intervenant',
                    ],
                    [
                        'assigned_hours' => 30,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            foreach ($modules as $module) {
                DB::table('group_module')->updateOrInsert(
                    [
                        'group_id' => $group->id,
                        'module_id' => $module->id,
                        'academic_year' => 2025,
                        'semester' => 1,
                    ],
                    [
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            $sessionDefs = [
                [
                    'topic' => 'DEMO — Séance CM Programmation',
                    'module_idx' => 0,
                    'teacher_idx' => 0,
                    'start' => Carbon::parse('2025-11-10 09:00:00'),
                    'end' => Carbon::parse('2025-11-10 10:30:00'),
                    'room' => 'Amphi A-Démo',
                    'status' => CourseSession::STATUS_COMPLETED,
                ],
                [
                    'topic' => 'DEMO — TD Mathématiques',
                    'module_idx' => 1,
                    'teacher_idx' => 1,
                    'start' => Carbon::parse('2025-11-12 14:00:00'),
                    'end' => Carbon::parse('2025-11-12 15:30:00'),
                    'room' => 'Salle B-Démo',
                    'status' => CourseSession::STATUS_COMPLETED,
                ],
            ];

            $sessionModels = [];
            foreach ($sessionDefs as $def) {
                $module = $modules[$def['module_idx']];
                $teacher = $teachers[$def['teacher_idx']];

                $session = CourseSession::firstOrCreate(
                    [
                        'topic' => $def['topic'],
                        'module_id' => $module->id,
                    ],
                    [
                        'group_id' => $group->id,
                        'teacher_id' => $teacher->id,
                        'date' => $def['start']->toDateString(),
                        'start_time' => $def['start'],
                        'end_time' => $def['end'],
                        'room' => $def['room'],
                        'description' => 'Séance générée pour la démonstration.',
                        'status' => $def['status'],
                    ]
                );

                $sessionModels[] = $session;

                $presentCount = $def['module_idx'] === 0 ? 3 : 4;
                foreach ($students as $idx => $student) {
                    $isPresent = $idx < $presentCount;
                    AttendanceRecord::firstOrCreate(
                        [
                            'session_id' => $session->id,
                            'student_id' => $student->id,
                        ],
                        [
                            'status' => $isPresent ? AttendanceRecord::STATUS_PRESENT : AttendanceRecord::STATUS_ABSENT,
                            'recorded_by' => $teacher->id,
                            'recorded_at' => $def['end'],
                            'is_late' => false,
                            'late_minutes' => null,
                        ]
                    );
                }

                $absentStudent = $students[3];
                if ($def['module_idx'] === 0) {
                    $attendance = AttendanceRecord::where('session_id', $session->id)
                        ->where('student_id', $absentStudent->id)
                        ->first();

                    $absence = Absence::firstOrCreate(
                        [
                            'student_id' => $absentStudent->id,
                            'session_id' => $session->id,
                        ],
                        [
                            'attendance_record_id' => $attendance?->id,
                            'status' => Absence::STATUS_UNJUSTIFIED,
                            'absence_type' => Absence::TYPE_ABSENCE,
                            'notes' => 'Absence enregistrée (données de démo).',
                        ]
                    );

                    if (! $absence->justification_id) {
                        $just = Justification::create([
                            'student_id' => $absentStudent->id,
                            'absence_id' => $absence->id,
                            'attendance_record_id' => $attendance?->id,
                            'type' => Justification::TYPE_MEDICAL,
                            'description' => 'Certificat médical (démo) — à valider par l\'administration.',
                            'start_date' => '2025-11-09',
                            'end_date' => '2025-11-10',
                            'submitted_at' => $now,
                            'status' => Justification::STATUS_PENDING,
                        ]);

                        $absence->update([
                            'justification_id' => $just->id,
                            'status' => Absence::STATUS_PENDING,
                        ]);
                    }
                }
            }

            $stu1UserId = $students[0]->user_id;
            if (! Notification::where('user_id', $stu1UserId)->where('type', 'demo_info')->exists()) {
                Notification::create([
                    'user_id' => $stu1UserId,
                    'title' => 'Bienvenue',
                    'message' => 'Bienvenue sur l\'environnement de démonstration.',
                    'type' => Notification::TYPE_INFO,
                    'data' => ['source' => 'DemoDataSeeder'],
                    'priority' => Notification::PRIORITY_NORMAL,
                ]);
            }

            if (! Notification::where('user_id', $admin->id)->where('type', 'demo_alert')->exists()) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'Justification en attente',
                    'message' => 'Une justification démo est en attente de traitement.',
                    'type' => Notification::TYPE_ALERT,
                    'data' => [],
                    'priority' => Notification::PRIORITY_HIGH,
                ]);
            }

            if (! DB::table('settings')->where('key', 'demo_environment')->exists()) {
                DB::table('settings')->insert([
                    'key' => 'demo_environment',
                    'value' => '1',
                    'type' => 'boolean',
                    'group' => 'general',
                    'description' => 'Indique que des données de démonstration ont été chargées.',
                    'is_editable' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            if (! DB::table('settings')->where('key', 'absence_warning_threshold')->exists()) {
                DB::table('settings')->insert([
                    'key' => 'absence_warning_threshold',
                    'value' => '85',
                    'type' => 'integer',
                    'group' => 'attendance',
                    'description' => 'Seuil d\'alerte présence (pourcentage).',
                    'is_editable' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    /**
     * Original project bootstrap accounts (additive only — skipped if emails/codes already exist).
     */
    private function seedLegacyBootstrapUsersIfMissing(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@estsb.ma'],
            [
                'name' => 'Administrateur ESTSB',
                'password' => Hash::make('admin123'),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]
        );

        $legacyTeachers = [
            [
                'name' => 'Dr. Fatima Alaoui',
                'email' => 'fatima.alaoui@estsb.ma',
                'teacher_code' => 'T001',
                'departement' => 'Informatique',
            ],
            [
                'name' => 'Pr. Mohamed Bennani',
                'email' => 'mohamed.bennani@estsb.ma',
                'teacher_code' => 'T002',
                'departement' => 'Electronique',
            ],
            [
                'name' => 'Dr. Rachid Tazi',
                'email' => 'rachid.tazi@estsb.ma',
                'teacher_code' => 'T003',
                'departement' => 'Mathematiques',
            ],
        ];

        foreach ($legacyTeachers as $teacherData) {
            $user = User::firstOrCreate(
                ['email' => $teacherData['email']],
                [
                    'name' => $teacherData['name'],
                    'password' => Hash::make('teacher123'),
                    'role' => User::ROLE_TEACHER,
                    'is_active' => true,
                ]
            );

            Teacher::firstOrCreate(
                ['teacher_code' => $teacherData['teacher_code']],
                [
                    'user_id' => $user->id,
                    'departement' => $teacherData['departement'],
                ]
            );
        }

        $legacyStudents = [
            [
                'name' => 'Ahmed Bennani',
                'email' => 'ahmed.bennani@estsb.ma',
                'student_number' => 'STU001',
                'filiere' => 'Genie Informatique',
                'niveau' => '1ere annee',
            ],
            [
                'name' => 'Sara Alaoui',
                'email' => 'sara.alaoui@estsb.ma',
                'student_number' => 'STU002',
                'filiere' => 'Genie Informatique',
                'niveau' => '1ere annee',
            ],
            [
                'name' => 'Youssef Tazi',
                'email' => 'youssef.tazi@estsb.ma',
                'student_number' => 'STU003',
                'filiere' => 'Genie Informatique',
                'niveau' => '1ere annee',
            ],
            [
                'name' => 'Fatima Zahra',
                'email' => 'fatima.zahra@estsb.ma',
                'student_number' => 'STU004',
                'filiere' => 'Genie Electrique',
                'niveau' => '1ere annee',
            ],
        ];

        foreach ($legacyStudents as $studentData) {
            $user = User::firstOrCreate(
                ['email' => $studentData['email']],
                [
                    'name' => $studentData['name'],
                    'password' => Hash::make('student123'),
                    'role' => User::ROLE_STUDENT,
                    'is_active' => true,
                ]
            );

            Student::firstOrCreate(
                ['student_number' => $studentData['student_number']],
                [
                    'user_id' => $user->id,
                    'filiere' => $studentData['filiere'],
                    'niveau' => $studentData['niveau'],
                    'academic_year' => 2025,
                ]
            );
        }
    }
}

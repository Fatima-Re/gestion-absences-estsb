<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Group;
use App\Models\Module;
use App\Models\Setting;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Administrateur ESTSB',
            'email' => 'admin@estsb.ma',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'phone' => '+212 6 00 00 00 00',
            'is_active' => true,
        ]);

        // Create sample groups
        $groups = [
            [
                'name' => 'GI-1',
                'filiere' => 'Génie Informatique',
                'niveau' => '1ère année',
                'academic_year' => 2024,
                'max_students' => 40,
                'current_students' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'GI-2',
                'filiere' => 'Génie Informatique',
                'niveau' => '2ème année',
                'academic_year' => 2024,
                'max_students' => 35,
                'current_students' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'GE-1',
                'filiere' => 'Génie Électrique',
                'niveau' => '1ère année',
                'academic_year' => 2024,
                'max_students' => 40,
                'current_students' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'GE-2',
                'filiere' => 'Génie Électrique',
                'niveau' => '2ème année',
                'academic_year' => 2024,
                'max_students' => 35,
                'current_students' => 0,
                'is_active' => true,
            ],
        ];

        foreach ($groups as $groupData) {
            Group::create($groupData);
        }

        // Create sample teachers
        $teachersData = [
            [
                'name' => 'Dr. Fatima Alaoui',
                'email' => 'fatima.alaoui@estsb.ma',
                'specialization' => 'Informatique',
                'teacher_code' => 'T001',
            ],
            [
                'name' => 'Pr. Mohamed Bennani',
                'email' => 'mohamed.bennani@estsb.ma',
                'specialization' => 'Électronique',
                'teacher_code' => 'T002',
            ],
            [
                'name' => 'Dr. Rachid Tazi',
                'email' => 'rachid.tazi@estsb.ma',
                'specialization' => 'Mathématiques',
                'teacher_code' => 'T003',
            ],
        ];

        foreach ($teachersData as $teacherData) {
            $user = User::create([
                'name' => $teacherData['name'],
                'email' => $teacherData['email'],
                'password' => Hash::make('teacher123'),
                'role' => 'teacher',
                'is_active' => true,
            ]);

            Teacher::create([
                'user_id' => $user->id,
                'teacher_code' => $teacherData['teacher_code'],
                'specialization' => $teacherData['specialization'],
            ]);
        }

        // Create sample modules
        $modules = [
            ['name' => 'Algorithmique et Programmation', 'code' => 'ALGO101', 'credits' => 6, 'semester' => 1],
            ['name' => 'Bases de Données', 'code' => 'BDD201', 'credits' => 4, 'semester' => 2],
            ['name' => 'Réseaux Informatiques', 'code' => 'RES301', 'credits' => 5, 'semester' => 3],
            ['name' => 'Électronique Numérique', 'code' => 'ELN101', 'credits' => 4, 'semester' => 1],
            ['name' => 'Mathématiques Discrètes', 'code' => 'MATH101', 'credits' => 5, 'semester' => 1],
        ];

        foreach ($modules as $moduleData) {
            Module::create($moduleData);
        }

        // Create sample students
        $studentsData = [
            [
                'name' => 'Ahmed Bennani',
                'email' => 'ahmed.bennani@estsb.ma',
                'student_number' => 'GI2024001',
                'group_id' => 1, // Génie Informatique - 1ère année
            ],
            [
                'name' => 'Sara Alaoui',
                'email' => 'sara.alaoui@estsb.ma',
                'student_number' => 'GI2024002',
                'group_id' => 1,
            ],
            [
                'name' => 'Youssef Tazi',
                'email' => 'youssef.tazi@estsb.ma',
                'student_number' => 'GI2024003',
                'group_id' => 1,
            ],
            [
                'name' => 'Fatima Zahra',
                'email' => 'fatima.zahra@estsb.ma',
                'student_number' => 'GE2024001',
                'group_id' => 3, // Génie Électrique - 1ère année
            ],
        ];

        foreach ($studentsData as $studentData) {
            $user = User::create([
                'name' => $studentData['name'],
                'email' => $studentData['email'],
                'password' => Hash::make('student123'),
                'role' => 'student',
                'is_active' => true,
            ]);

            Student::create([
                'user_id' => $user->id,
                'student_number' => $studentData['student_number'],
                'group_id' => $studentData['group_id'],
            ]);
        }

        // Create default settings
        $settings = [
            [
                'key' => 'max_absences_per_module',
                'value' => '5',
                'type' => 'integer',
                'description' => 'Nombre maximum d\'absences autorisées par module',
                'group' => 'attendance',
                'is_editable' => true,
            ],
            [
                'key' => 'justification_deadline_days',
                'value' => '7',
                'type' => 'integer',
                'description' => 'Délai en jours pour soumettre une justification',
                'group' => 'attendance',
                'is_editable' => true,
            ],
            [
                'key' => 'auto_mark_absent_minutes',
                'value' => '15',
                'type' => 'integer',
                'description' => 'Minutes après lesquelles un étudiant est marqué absent automatiquement',
                'group' => 'attendance',
                'is_editable' => true,
            ],
            [
                'key' => 'email_notifications',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Activer les notifications par email',
                'group' => 'notifications',
                'is_editable' => true,
            ],
            [
                'key' => 'absence_alerts',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Envoyer des alertes en cas d\'absence',
                'group' => 'notifications',
                'is_editable' => true,
            ],
            [
                'key' => 'justification_reminders',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Rappels pour les justifications en attente',
                'group' => 'notifications',
                'is_editable' => true,
            ],
            [
                'key' => 'session_lifetime',
                'value' => '120',
                'type' => 'integer',
                'description' => 'Durée de session en minutes',
                'group' => 'system',
                'is_editable' => true,
            ],
        ];

        foreach ($settings as $settingData) {
            Setting::create($settingData);
        }

        // Assign modules to teachers and groups
        $teacher1 = Teacher::where('teacher_code', 'T001')->first();
        $teacher2 = Teacher::where('teacher_code', 'T002')->first();
        $teacher3 = Teacher::where('teacher_code', 'T003')->first();

        $module1 = Module::where('code', 'ALGO101')->first();
        $module2 = Module::where('code', 'BDD201')->first();
        $module3 = Module::where('code', 'ELN101')->first();

        $group1 = Group::where('name', 'GI-1')->first();
        $group2 = Group::where('name', 'GE-1')->first();

        // Assign teacher-module relationships (Teacher has modules relationship)
        if ($teacher1 && $module1) {
            $teacher1->modules()->attach($module1->id, ['is_responsible' => true]);
        }
        if ($teacher2 && $module3) {
            $teacher2->modules()->attach($module3->id, ['is_responsible' => true]);
        }
        if ($teacher3 && $module1) {
            $teacher3->modules()->attach($module1->id, ['is_responsible' => false]);
        }

        // Assign module-group relationships (Module has groups relationship)
        if ($module1 && $group1) {
            $module1->groups()->attach($group1->id);
        }
        if ($module3 && $group2) {
            $module3->groups()->attach($group2->id);
        }
        if ($module2 && $group1) {
            $module2->groups()->attach($group1->id);
        }
    }
}

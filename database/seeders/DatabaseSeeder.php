<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reset seeded data to keep this seeder idempotent.
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ([
            'absences',
            'attendance_records',
            'justifications',
            'course_sessions',
            'notifications',
            'module_teacher',
            'group_module',
            'group_student',
            'students',
            'teachers',
            'modules',
            'groups',
            'settings',
            'users',
        ] as $table) {
            DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        User::create([
            'name' => 'Administrateur ESTSB',
            'email' => 'admin@estsb.ma',
            'password' => Hash::make('admin123'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $teachers = [
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

        foreach ($teachers as $teacherData) {
            $user = User::create([
                'name' => $teacherData['name'],
                'email' => $teacherData['email'],
                'password' => Hash::make('teacher123'),
                'role' => User::ROLE_TEACHER,
                'is_active' => true,
            ]);

            Teacher::create([
                'user_id' => $user->id,
                'teacher_code' => $teacherData['teacher_code'],
                'departement' => $teacherData['departement'],
            ]);
        }

        $students = [
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

        foreach ($students as $studentData) {
            $user = User::create([
                'name' => $studentData['name'],
                'email' => $studentData['email'],
                'password' => Hash::make('student123'),
                'role' => User::ROLE_STUDENT,
                'is_active' => true,
            ]);

            Student::create([
                'user_id' => $user->id,
                'student_number' => $studentData['student_number'],
                'filiere' => $studentData['filiere'],
                'niveau' => $studentData['niveau'],
                'academic_year' => 2025,
            ]);
        }
    }
}

<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'student_number' => 'STU'.fake()->unique()->numerify('######'),
            'filiere' => 'Génie Informatique',
            'niveau' => '1ère année',
            'academic_year' => (int) now()->format('Y'),
        ];
    }
}

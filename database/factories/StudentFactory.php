<?php

namespace Database\Factories;

use App\Models\School;
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
            'school_id' => School::factory(),
            'user_id' => null,
            'external_id' => (string) fake()->unique()->numerify('S####'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'grade_level' => fake()->randomElement(['9', '10', '11', '12']),
            'status' => 'active',
        ];
    }
}

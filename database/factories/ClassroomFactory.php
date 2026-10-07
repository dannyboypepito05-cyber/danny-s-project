<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\Instructor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Classroom>
 */
class ClassroomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'instructor_id' => Instructor::factory(),
            'name' => fake()->sentence(3),
            'code' => fake()->unique()->bothify('CLS-###'),
            'subject' => fake()->randomElement(['Mathematics', 'Science', 'English', 'Computer Studies']),
            'grade_level' => fake()->randomElement(['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10']),
            'capacity' => fake()->numberBetween(15, 45),
            'schedule' => 'Mon / Wed - 09:00 AM',
            'delivery_mode' => 'Hybrid',
            'status' => 'Active',
            'description' => fake()->paragraph(),
        ];
    }
}

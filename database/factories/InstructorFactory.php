<?php

namespace Database\Factories;

use App\Models\Instructor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Instructor>
 */
class InstructorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'specialization' => fake()->randomElement(['Mathematics', 'Science', 'English', 'Computer Studies']),
            'qualification' => fake()->randomElement(['B.Ed', 'M.Ed', 'M.Sc', 'Ph.D']),
            'years_experience' => fake()->numberBetween(1, 20),
            'quality_score' => fake()->randomFloat(2, 3, 5),
            'status' => 'Active',
            'bio' => fake()->sentence(),
        ];
    }
}

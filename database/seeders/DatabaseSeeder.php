<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Program;
use App\Models\Course;
use App\Models\Section;
use App\Models\User;
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
        User::factory()->create([
            'name' => 'DUCQUE Administrator',
            'email' => 'admin@ducque.test',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMINISTRATOR,
        ]);

        User::factory()->create([
            'name' => 'DUCQUE Instructor',
            'email' => 'instructor@ducque.test',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_INSTRUCTOR,
        ]);

        User::factory()->create([
            'name' => 'DUCQUE Student',
            'email' => 'student@ducque.test',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_STUDENT,
        ]);

        $it = Department::firstOrCreate([
            'code' => 'IT',
        ], [
            'name' => 'Information Technology',
            'description' => 'Digital systems, data management, and computing programs.',
            'status' => 'Active',
        ]);

        $business = Department::firstOrCreate([
            'code' => 'BUS',
        ], [
            'name' => 'Business Administration',
            'description' => 'Leadership, operations, and business strategy disciplines.',
            'status' => 'Active',
        ]);

        $program = Program::firstOrCreate([
            'code' => 'BSIT',
        ], [
            'department_id' => $it->id,
            'name' => 'Bachelor of Science in Information Technology',
            'description' => 'A comprehensive IT program that combines systems, software, and infrastructure training.',
            'status' => 'Active',
        ]);

        Course::firstOrCreate([
            'code' => 'IT101',
        ], [
            'department_id' => $it->id,
            'title' => 'Introduction to Information Technology',
            'description' => 'Foundations of modern computing and information systems.',
            'units' => 3,
            'status' => 'Active',
        ]);

        Course::firstOrCreate([
            'code' => 'IT201',
        ], [
            'department_id' => $it->id,
            'title' => 'Database Management Systems',
            'description' => 'Relational database design, querying, and administration.',
            'units' => 3,
            'status' => 'Active',
        ]);

        Section::firstOrCreate([
            'name' => 'BSIT-101',
        ], [
            'program_id' => $program->id,
            'year_level' => '1st Year',
            'academic_year' => '2026-2027',
            'semester' => '1st Semester',
            'status' => 'Active',
        ]);
    }
}

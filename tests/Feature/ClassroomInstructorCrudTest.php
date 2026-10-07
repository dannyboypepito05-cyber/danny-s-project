<?php

use App\Models\Classroom;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows an administrator to create and update an instructor', function () {
    $administrator = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);

    $this->actingAs($administrator)->post(route('instructors.store'), [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'specialization' => 'Computer Studies',
        'qualification' => 'M.Sc',
        'years_experience' => 12,
        'quality_score' => 4.75,
        'status' => 'Active',
    ])->assertRedirect(route('instructors.index', absolute: false));

    $instructor = Instructor::firstOrFail();
    expect($instructor->quality_score)->toBe('4.75');

    $this->actingAs($administrator)->put(route('instructors.update', $instructor), [
        'name' => 'Ada Byron Lovelace',
        'email' => 'ada@example.com',
        'specialization' => 'Computer Studies',
        'qualification' => 'Ph.D',
        'years_experience' => 13,
        'quality_score' => 4.9,
        'status' => 'Active',
    ])->assertRedirect(route('instructors.index', absolute: false));

    expect($instructor->fresh()->name)->toBe('Ada Byron Lovelace');
});

it('rejects inactive instructors when creating a classroom', function () {
    $administrator = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);
    $inactiveInstructor = Instructor::factory()->create(['status' => 'Inactive']);

    $this->actingAs($administrator)
        ->post(route('classrooms.store'), [
            'instructor_id' => $inactiveInstructor->id,
            'name' => 'Digital Algebra Lab',
            'code' => 'ALG-101',
            'subject' => 'Mathematics',
            'grade_level' => 'Grade 9',
            'capacity' => 35,
            'schedule' => 'Tuesday - 10:00 AM',
            'delivery_mode' => 'Hybrid',
            'status' => 'Active',
        ])
        ->assertSessionHasErrors('instructor_id');

    expect(Classroom::count())->toBe(0);
});

it('allows an administrator to create and delete a classroom', function () {
    $administrator = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);
    $instructor = Instructor::factory()->create();

    $this->actingAs($administrator)->post(route('classrooms.store'), [
        'instructor_id' => $instructor->id,
        'name' => 'Digital Algebra Lab',
        'code' => 'ALG-101',
        'subject' => 'Mathematics',
        'grade_level' => 'Grade 9',
        'capacity' => 35,
        'schedule' => 'Tuesday - 10:00 AM',
        'delivery_mode' => 'Hybrid',
        'status' => 'Active',
    ])->assertRedirect(route('classrooms.index', absolute: false));

    $classroom = Classroom::firstOrFail();
    expect($classroom->instructor_id)->toBe($instructor->id);

    $this->actingAs($administrator)->delete(route('classrooms.destroy', $classroom))
        ->assertRedirect(route('classrooms.index', absolute: false));

    expect(Classroom::count())->toBe(0);
});

it('forbids non administrators from managing classrooms', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $this->actingAs($student)->get(route('classrooms.index'))->assertForbidden();
});

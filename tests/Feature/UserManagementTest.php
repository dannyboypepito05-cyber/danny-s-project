<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows an administrator to list, create, update, and delete users', function () {
    $administrator = User::factory()->create([
        'name' => 'System Administrator',
        'role' => User::ROLE_ADMINISTRATOR,
    ]);
    $registeredUser = User::factory()->create(['name' => 'Existing Student']);

    $this->actingAs($administrator)
        ->get(route('users.index'))
        ->assertOk()
        ->assertSee('System Administrator')
        ->assertSee('Existing Student')
        ->assertSee(route('users.show', $registeredUser), false)
        ->assertSee(route('users.edit', $registeredUser), false)
        ->assertSee('Delete this user?', false);

    $this->actingAs($administrator)
        ->get(route('users.show', $registeredUser))
        ->assertOk()
        ->assertSee('Existing Student')
        ->assertSee($registeredUser->email);

    $this->actingAs($administrator)
        ->get(route('users.edit', $registeredUser))
        ->assertOk()
        ->assertSee('Update user')
        ->assertSee('name="role"', false);

    $this->actingAs($administrator)
        ->post(route('users.store'), [
            'name' => 'New Staff Member',
            'email' => 'staff@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_STAFF,
        ])
        ->assertRedirect(route('users.index', absolute: false));

    $createdUser = User::where('email', 'staff@example.com')->firstOrFail();
    expect($createdUser->role)->toBe(User::ROLE_STAFF);

    $this->actingAs($administrator)
        ->put(route('users.update', $registeredUser), [
            'name' => 'Updated Student',
            'email' => $registeredUser->email,
            'role' => User::ROLE_ADMINISTRATOR,
        ])
        ->assertRedirect(route('users.index', absolute: false));

    expect($registeredUser->fresh()->name)->toBe('Updated Student')
        ->and($registeredUser->fresh()->role)->toBe(User::ROLE_ADMINISTRATOR);

    $this->actingAs($administrator)
        ->delete(route('users.destroy', $createdUser))
        ->assertRedirect(route('users.index', absolute: false));

    expect(User::find($createdUser->id))->toBeNull();
});

it('allows an administrator to assign the instructor role to a user', function () {
    $administrator = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);

    $this->actingAs($administrator)
        ->post(route('users.store'), [
            'name' => 'Instructor User',
            'email' => 'instructor.user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => User::ROLE_INSTRUCTOR,
        ])
        ->assertRedirect(route('users.index', absolute: false));

    $instructorUser = User::where('email', 'instructor.user@example.com')->firstOrFail();
    expect($instructorUser->role)->toBe(User::ROLE_INSTRUCTOR);

    $this->actingAs($administrator)
        ->put(route('users.update', $instructorUser), [
            'name' => 'Updated Instructor User',
            'email' => $instructorUser->email,
            'role' => User::ROLE_INSTRUCTOR,
        ])
        ->assertRedirect(route('users.index', absolute: false));

    expect($instructorUser->fresh()->name)->toBe('Updated Instructor User');
});

it('forbids non administrators from managing users', function () {
    $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
    $otherUser = User::factory()->create();

    $this->actingAs($student)
        ->get(route('users.index'))
        ->assertForbidden();

    $this->actingAs($student)
        ->delete(route('users.destroy', $otherUser))
        ->assertForbidden();

    expect(User::find($otherUser->id))->not->toBeNull();
});

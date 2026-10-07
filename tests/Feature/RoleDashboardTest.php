<?php

use App\Models\Classroom;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('services.openweather.key', 'test-openweather-key');
});

it('redirects each role to its dashboard after login', function (string $role, string $route, string $message) {
    Http::fake([
        'api.openweathermap.org/data/2.5/weather*' => Http::response(['message' => 'unavailable'], 503),
    ]);
    $user = User::factory()->create([
        'role' => $role,
        'password' => Hash::make('password'),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'role' => $role,
    ]);

    $response->assertRedirect(route($route, absolute: false));
    $this->get(route($route, absolute: false))
        ->assertOk()
        ->assertSee($message);
})->with([
    [User::ROLE_ADMINISTRATOR, 'dashboards.administrator', 'Welcome Administrator!'],
    [User::ROLE_STAFF, 'dashboards.staff', 'Welcome Staff!'],
    [User::ROLE_INSTRUCTOR, 'dashboards.staff', 'Welcome Staff!'],
    [User::ROLE_STUDENT, 'dashboards.student', 'Welcome Student!'],
]);

it('redirects authenticated users from the generic dashboard to their role dashboard', function (string $role, string $route) {
    Http::fake([
        'api.openweathermap.org/data/2.5/weather*' => Http::response(['message' => 'unavailable'], 503),
    ]);
    $user = User::factory()->create(['role' => $role]);

    $this->actingAs($user)
        ->get(route('dashboard', absolute: false))
        ->assertRedirect(route($route, absolute: false));
})->with([
    [User::ROLE_ADMINISTRATOR, 'dashboards.administrator'],
    [User::ROLE_STAFF, 'dashboards.staff'],
    [User::ROLE_INSTRUCTOR, 'dashboards.staff'],
    [User::ROLE_STUDENT, 'dashboards.student'],
]);

it('forbids every role from another role dashboard', function (string $role, string $route) {
    $user = User::factory()->create(['role' => $role]);

    $this->actingAs($user)
        ->get(route($route, absolute: false))
        ->assertForbidden();
})->with([
    [User::ROLE_ADMINISTRATOR, 'dashboards.staff'],
    [User::ROLE_ADMINISTRATOR, 'dashboards.student'],
    [User::ROLE_STAFF, 'dashboards.administrator'],
    [User::ROLE_STAFF, 'dashboards.student'],
    [User::ROLE_INSTRUCTOR, 'dashboards.administrator'],
    [User::ROLE_INSTRUCTOR, 'dashboards.student'],
    [User::ROLE_STUDENT, 'dashboards.administrator'],
    [User::ROLE_STUDENT, 'dashboards.staff'],
]);

it('rejects login when the selected role does not match the account', function () {
    $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'role' => User::ROLE_STAFF,
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('renders academic management pages after their migrations have been applied', function () {
    $administrator = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);

    foreach (['departments.index', 'programs.index', 'courses.index', 'sections.index'] as $route) {
        $this->actingAs($administrator)
            ->get(route($route, absolute: false))
            ->assertOk();
    }
});

it('shows live administrator metrics instead of sample figures', function () {
    Http::fake([
        'api.openweathermap.org/data/2.5/weather*' => Http::response(['message' => 'unavailable'], 503),
    ]);
    $administrator = User::factory()->create(['role' => User::ROLE_ADMINISTRATOR]);
    User::factory()->create(['role' => User::ROLE_STUDENT]);
    $instructor = Instructor::factory()->create(['quality_score' => 4.5]);
    Classroom::factory()->for($instructor)->create();

    $this->actingAs($administrator)
        ->get(route('dashboards.administrator', absolute: false))
        ->assertViewHas('metrics', function (array $metrics): bool {
            return $metrics[0]['value'] === 1
                && $metrics[1]['value'] === 1
                && $metrics[2]['value'] === 1
                && $metrics[3]['value'] === '4.50 / 5';
        });
});

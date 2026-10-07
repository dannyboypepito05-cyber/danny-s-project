<?php

use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\WeatherLocationController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    return match ($user?->role) {
        User::ROLE_ADMINISTRATOR => redirect()->route('dashboards.administrator'),
        User::ROLE_STAFF => redirect()->route('dashboards.staff'),
        User::ROLE_INSTRUCTOR => redirect()->route('dashboards.staff'),
        User::ROLE_STUDENT => redirect()->route('dashboards.student'),
        default => abort(403, 'Your account has no authorized dashboard.'),
    };
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/weather', [WeatherController::class, 'index'])->name('weather.index');
    Route::get('/weather/history', [WeatherController::class, 'history'])->name('weather.history');
    Route::get('/weather/alerts', [WeatherController::class, 'alerts'])->name('weather.alerts');
    Route::get('/weather/status', [WeatherController::class, 'status'])->middleware('throttle:60,1')->name('weather.status');
    Route::post('/weather/sync', [WeatherController::class, 'sync'])->middleware('throttle:6,1')->name('weather.sync');

    Route::middleware('role:'.User::ROLE_ADMINISTRATOR)->group(function () {
        Route::post('/administrator/weather/search', [WeatherController::class, 'search'])
            ->middleware('throttle:6,1')
            ->name('weather.search');
        Route::get('/weather/settings', [WeatherController::class, 'settings'])->name('weather.settings');
        Route::put('/weather/settings', [WeatherController::class, 'updateSettings'])->name('weather.settings.update');
        Route::resource('weather-locations', WeatherLocationController::class);
    });

    Route::get('/administrator/dashboard', [DashboardController::class, 'administrator'])
        ->middleware('role:'.User::ROLE_ADMINISTRATOR)
        ->name('dashboards.administrator');

    Route::get('/staff/dashboard', [DashboardController::class, 'staff'])
        ->middleware('role:'.User::ROLE_STAFF.','.User::ROLE_INSTRUCTOR)
        ->name('dashboards.staff');

    Route::get('/student/dashboard', [DashboardController::class, 'student'])
        ->middleware('role:'.User::ROLE_STUDENT)
        ->name('dashboards.student');
});

Route::middleware('auth')->group(function () {

    // Academic and administrative management
    Route::resource('users', UserController::class)->middleware('role:Administrator');
    Route::resource('departments', DepartmentController::class)->middleware('role:Administrator');
    Route::resource('programs', ProgramController::class)->middleware('role:Administrator');
    Route::resource('courses', CourseController::class)->middleware('role:Administrator');
    Route::resource('sections', SectionController::class)->middleware('role:Administrator');
    Route::resource('instructors', InstructorController::class)->middleware('role:Administrator');
    Route::resource('classrooms', ClassroomController::class)->middleware('role:Administrator');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__.'/auth.php';
http:// example_app.test/classrooms

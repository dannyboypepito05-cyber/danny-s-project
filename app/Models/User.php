<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    public const ROLE_ADMINISTRATOR = 'Administrator';
    public const ROLE_INSTRUCTOR = 'Instructor';
    public const ROLE_STAFF = 'Staff';
    public const ROLE_STUDENT = 'Student';

    use HasFactory, Notifiable;

    public static function normalizeRole(string $role): string
    {
        return match (strtolower(trim($role))) {
            'admin', 'administrator' => self::ROLE_ADMINISTRATOR,
            'instructor' => self::ROLE_INSTRUCTOR,
            'staff' => self::ROLE_STAFF,
            'student' => self::ROLE_STUDENT,
            default => trim($role),
        };
    }

    public static function validRoles(): array
    {
        return [
            self::ROLE_ADMINISTRATOR,
            self::ROLE_INSTRUCTOR,
            self::ROLE_STAFF,
            self::ROLE_STUDENT,
        ];
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $attributes = [
        'role' => self::ROLE_STUDENT,
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

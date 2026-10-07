<?php

namespace App\Models;

use Database\Factories\InstructorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    /** @use HasFactory<InstructorFactory> */
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone', 'specialization', 'qualification',
        'years_experience', 'quality_score', 'status', 'bio',
    ];

    protected function casts(): array
    {
        return [
            'quality_score' => 'decimal:2',
            'years_experience' => 'integer',
        ];
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }
}

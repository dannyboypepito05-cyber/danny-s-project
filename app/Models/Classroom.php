<?php

namespace App\Models;

use Database\Factories\ClassroomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    /** @use HasFactory<ClassroomFactory> */
    use HasFactory;

    protected $fillable = [
        'instructor_id', 'name', 'code', 'subject', 'grade_level',
        'capacity', 'schedule', 'delivery_mode', 'status', 'description',
    ];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }
}

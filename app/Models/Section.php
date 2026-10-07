<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'program_id',
        'name',
        'year_level',
        'academic_year',
        'semester',
        'status',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}

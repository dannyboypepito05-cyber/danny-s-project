<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
    ];

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}

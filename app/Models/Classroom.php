<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    protected $fillable = [
        'name',
        'grade_level',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}

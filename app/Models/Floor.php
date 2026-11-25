<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Floor extends Model
{
    public const MAP_NAMES = [
        'Lantai 1',
        'Lantai 2',
        'Lantai 3',
        'Lantai 4',
    ];

    protected $fillable = [
        'name',
        'level',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}

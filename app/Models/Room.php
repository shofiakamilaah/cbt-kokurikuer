<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Room extends Model
{
    protected $fillable = [
        'floor_id',
        'name',
        'pos_x',
        'pos_y',
        'width',
        'height',
    ];

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }
}

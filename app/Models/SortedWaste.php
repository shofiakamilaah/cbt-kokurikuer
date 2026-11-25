<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SortedWaste extends Model
{
    protected $fillable = [
        'classroom_id',
        'user_id',
        'sorted_date',
        'organic_weight',
        'inorganic_weight',
        'residue_weight',
    ];

    protected function casts(): array
    {
        return [
            'sorted_date' => 'date',
            'organic_weight' => 'decimal:2',
            'inorganic_weight' => 'decimal:2',
            'residue_weight' => 'decimal:2',
        ];
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

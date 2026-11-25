<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WednesdayCollection extends Model
{
    protected $fillable = [
        'contributor_id',
        'user_id',
        'collection_date',
        'waste_type',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'collection_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public const WASTE_LABELS = [
        'cooking_oil' => 'Minyak jelantah',
        'eggshell' => 'Cangkang telur',
    ];

    public const WASTE_UNITS = [
        'cooking_oil' => 'liter',
    ];

    public function getWasteLabelAttribute(): string
    {
        return self::WASTE_LABELS[$this->waste_type] ?? $this->waste_type;
    }

    public function getUnitAttribute(): string
    {
        return self::WASTE_UNITS[$this->waste_type] ?? '';
    }

    public function contributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contributor_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

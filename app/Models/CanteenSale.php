<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CanteenSale extends Model
{
    protected $fillable = [
        'user_id',
        'canteen_id',
        'sale_date',
        'item_name',
        'quantity',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'quantity' => 'integer',
            'price' => 'integer',
        ];
    }

    public function getTotalAttribute(): int
    {
        return $this->quantity * $this->price;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function canteen(): BelongsTo
    {
        return $this->belongsTo(Canteen::class);
    }
}

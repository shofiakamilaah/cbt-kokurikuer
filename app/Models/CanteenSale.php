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
        'initial_stock',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'quantity' => 'integer',
            'price' => 'integer',
            'initial_stock' => 'integer',
        ];
    }

    public function getTotalAttribute(): int
    {
        return (int) $this->quantity * (int) $this->price;
    }

    public function getRemainingStockAttribute(): int
    {
        return max(0, (int) $this->initial_stock - (int) $this->quantity);
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

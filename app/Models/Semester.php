<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $fillable = [
        'academic_year',
        'semester_number',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * The semester containing today, or null.
     */
    public static function current(): ?self
    {
        return static::where('start_date', '<=', today()->toDateString())
            ->where('end_date', '>=', today()->toDateString())
            ->first();
    }

    /**
     * Label like "Semester 1 TA 2026/2027".
     */
    public function getLabelAttribute(): string
    {
        return "Semester {$this->semester_number} TA {$this->academic_year}";
    }
}

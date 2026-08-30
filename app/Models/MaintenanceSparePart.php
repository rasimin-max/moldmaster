<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceSparePart extends Model
{
    protected $fillable = [
        'maintenance_id', 'part_name', 'part_number', 'quantity',
        'unit', 'unit_price', 'subtotal', 'vendor',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (MaintenanceSparePart $part) {
            $part->subtotal = $part->quantity * $part->unit_price;
        });

        static::saved(function (MaintenanceSparePart $part) {
            $part->maintenance->recalculateCost();
        });
    }

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class);
    }
}

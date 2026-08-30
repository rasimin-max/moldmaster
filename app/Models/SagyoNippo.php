<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SagyoNippo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'total_hours',
        'photo',
    ];

    protected $casts = [
        'date' => 'date',
        'hours' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SagyoNippoItem::class);
    }

    public function getCostAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->hours * ($item->jobCode?->rate ?? 0);
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsAuditActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mold extends Model
{
    use HasFactory, SoftDeletes, LogsAuditActivity;

    protected $fillable = [
        'mold_number', 'project_id', 'code', 'name', 'project_name', 'customer', 'product_type',
        'cavity', 'shot_life', 'current_shot', 'status', 'description', 'photo'
    ];

    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(Component::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function sagyoNippoItems(): HasMany
    {
        return $this->hasMany(SagyoNippoItem::class);
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'success',
            'maintenance' => 'warning',
            'inactive' => 'gray',
            'retired' => 'danger',
            default => 'gray',
        };
    }

    public function getShotProgressAttribute(): int
    {
        if (!$this->shot_life || $this->shot_life === 0) return 0;
        return (int) min(100, ($this->current_shot / $this->shot_life) * 100);
    }

    public function getTotalManufacturingCostAttribute(): float
    {
        return $this->sagyoNippoItems->sum(function ($item) {
            $rate = $item->jobCode?->rate ?? 0;
            return $item->hours * $rate;
        });
    }
}

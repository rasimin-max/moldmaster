<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAuditActivity;

class Tool extends Model
{
    use HasFactory, SoftDeletes, LogsAuditActivity;

    protected $fillable = [
        'code', 'name', 'type', 'category', 'total_quantity', 'available_quantity', 'min_stock',
        'condition', 'location', 'description', 'photo',
    ];

    public function loans(): HasMany
    {
        return $this->hasMany(ToolLoan::class);
    }

    public function getConditionBadgeColorAttribute(): string
    {
        return match($this->condition) {
            'good' => 'success',
            'fair' => 'warning',
            'poor' => 'danger',
            'damaged' => 'danger',
            default => 'gray',
        };
    }
}

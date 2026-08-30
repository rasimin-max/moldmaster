<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAuditActivity;

class MachinePart extends Model
{
    use HasFactory, SoftDeletes, LogsAuditActivity;

    protected $fillable = [
        'machine_id',
        'name',
        'part_number',
        'installed_at',
        'expected_life_hours',
        'expected_life_cycles',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'installed_at' => 'date',
            'expected_life_hours' => 'integer',
            'expected_life_cycles' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }
}

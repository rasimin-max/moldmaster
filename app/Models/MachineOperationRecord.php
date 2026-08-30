<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAuditActivity;
use Carbon\Carbon;

class MachineOperationRecord extends Model
{
    use HasFactory, SoftDeletes, LogsAuditActivity;

    protected $fillable = [
        'machine_id',
        'user_id',
        'project_id',
        'mold_id',
        'component_id',
        'machine_program_id',
        'start_time',
        'end_time',
        'planned_duration_minutes',
        'duration_minutes',
        'operation_type',
        'cycles',
        'status',
        'notes',
        'photo',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'planned_duration_minutes' => 'decimal:2',
            'duration_minutes' => 'integer',
            'cycles' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (MachineOperationRecord $record) {
            if ($record->status === 'completed' && empty($record->duration_minutes) && $record->end_time && $record->start_time) {
                // Calculate duration in minutes if not filled manually
                $record->duration_minutes = Carbon::parse($record->start_time)->diffInMinutes(Carbon::parse($record->end_time));
            }
        });
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function mold(): BelongsTo
    {
        return $this->belongsTo(Mold::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    public function machineProgram(): BelongsTo
    {
        return $this->belongsTo(MachineProgram::class);
    }
}

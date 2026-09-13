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
        'barcode',
        'shift',
        'manual_hours',
        'manual_minutes',
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
            // Auto-fill barcode from MachineProgram if empty
            if (empty($record->barcode) && $record->machine_program_id) {
                $program = \App\Models\MachineProgram::find($record->machine_program_id);
                if ($program) {
                    $record->barcode = $program->barcode;
                }
            }

            // Calculate duration from manual hours/minutes if provided
            if ($record->manual_hours !== null || $record->manual_minutes !== null) {
                $hours = $record->manual_hours ?? 0;
                $minutes = $record->manual_minutes ?? 0;
                $record->duration_minutes = ($hours * 60) + $minutes;
            } elseif ($record->status === 'completed' && empty($record->duration_minutes) && $record->end_time && $record->start_time) {
                // Fallback to start/end time calculation
                $record->duration_minutes = (int) round(\Carbon\Carbon::parse($record->start_time)->diffInMinutes(\Carbon\Carbon::parse($record->end_time), true));
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

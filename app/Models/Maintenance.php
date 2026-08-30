<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsAuditActivity;

class Maintenance extends Model
{
    use HasFactory, LogsAuditActivity;

    protected $fillable = [
        'work_order_number', 'machine_id', 'reported_by', 'approved_by', 'technician_id',
        'type', 'status', 'priority', 'problem_description', 'action_taken', 'photo',
        'reported_at', 'approved_at', 'started_at', 'completed_at',
        'downtime_hours', 'labor_cost', 'spare_parts_cost', 'total_cost',
        'rejection_reason', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
            'approved_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'downtime_hours' => 'decimal:2',
            'labor_cost' => 'decimal:2',
            'spare_parts_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Maintenance $m) {
            if (empty($m->work_order_number)) {
                $year = now()->format('Y');
                $count = static::whereYear('created_at', $year)->count() + 1;
                $m->work_order_number = 'WO-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });

        static::updated(function (Maintenance $m) {
            // Update machine status based on maintenance status
            if ($m->wasChanged('status')) {
                $machineStatus = match($m->status) {
                    'approved', 'in_progress' => 'maintenance',
                    'completed' => 'operational',
                    default => null,
                };
                if ($machineStatus) {
                    $m->machine->update(['status' => $machineStatus]);
                }
            }
        });
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function spareParts(): HasMany
    {
        return $this->hasMany(MaintenanceSparePart::class);
    }

    public function getPriorityBadgeColorAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'gray',
            default => 'gray',
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'in_progress' => 'primary',
            'completed' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    public function recalculateCost(): void
    {
        $sparePartsCost = $this->spareParts()->sum('subtotal');
        $this->update([
            'spare_parts_cost' => $sparePartsCost,
            'total_cost' => $this->labor_cost + $sparePartsCost,
        ]);
    }
}

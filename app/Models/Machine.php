<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsAuditActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class Machine extends Model
{
    use HasFactory, SoftDeletes, LogsAuditActivity;

    protected $fillable = [
        'code', 'name', 'type', 'brand', 'model_number', 'serial_number',
        'area', 'year_purchased', 'status', 'hourly_rate', 'notes', 'photo',
    ];

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function operationRecords(): HasMany
    {
        return $this->hasMany(MachineOperationRecord::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(MachinePart::class);
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'operational' => 'success',
            'maintenance' => 'warning',
            'breakdown' => 'danger',
            'idle' => 'info',
            'retired' => 'gray',
            default => 'gray',
        };
    }

    public function getActiveMaintenanceAttribute(): ?Maintenance
    {
        return $this->maintenances()
            ->whereIn('status', ['pending', 'approved', 'in_progress'])
            ->latest()
            ->first();
    }

    public function getTotalOperationHoursAttribute(): int
    {
        // total minutes converted to hours
        $totalMinutes = $this->operationRecords()
            ->where('status', 'completed')
            ->sum('duration_minutes');
            
        return (int) floor($totalMinutes / 60);
    }

    public function getTotalOperationCyclesAttribute(): int
    {
        return (int) $this->operationRecords()
            ->where('status', 'completed')
            ->sum('cycles');
    }
}

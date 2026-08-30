<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Traits\LogsAuditActivity;

class ToolMovement extends Model
{
    use HasFactory, LogsAuditActivity;

    protected $fillable = [
        'reference_number', 'tool_id', 'requested_by', 'approved_by', 'type', 'status', 'quantity',
        'quantity_before', 'quantity_after', 'purpose', 'operator_name', 'photo', 'notes', 'rejection_reason',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ToolMovement $movement) {
            if (empty($movement->reference_number)) {
                $prefix = match($movement->type) {
                    'in' => 'T-IN',
                    'out' => 'T-OUT',
                    'adjustment' => 'T-ADJ',
                    default => 'T-TRX',
                };
                $movement->reference_number = $prefix . '-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
            }
        });

        static::created(function (ToolMovement $movement) {
            if ($movement->status === 'approved') {
                $tool = $movement->tool;
                if ($tool) {
                    $qtyBefore = $tool->available_quantity;

                    match($movement->type) {
                        'in' => $tool->increment('available_quantity', $movement->quantity) && $tool->increment('total_quantity', $movement->quantity),
                        'out' => $tool->decrement('available_quantity', $movement->quantity) && $tool->decrement('total_quantity', $movement->quantity),
                        'adjustment' => $tool->update(['available_quantity' => $movement->quantity, 'total_quantity' => $movement->quantity]),
                        default => null,
                    };

                    $tool->refresh();
                    $movement->updateQuietly([
                        'quantity_before' => $qtyBefore,
                        'quantity_after' => $tool->available_quantity,
                        'approved_at' => $movement->approved_at ?? now(),
                    ]);
                }
            }
        });

        static::updated(function (ToolMovement $movement) {
            if ($movement->wasChanged('status') && $movement->status === 'approved') {
                $tool = $movement->tool;
                $qtyBefore = $tool->available_quantity;

                match($movement->type) {
                    'in' => $tool->increment('available_quantity', $movement->quantity) && $tool->increment('total_quantity', $movement->quantity),
                    'out' => $tool->decrement('available_quantity', $movement->quantity) && $tool->decrement('total_quantity', $movement->quantity),
                    'adjustment' => $tool->update(['available_quantity' => $movement->quantity, 'total_quantity' => $movement->quantity]),
                    default => null,
                };

                $tool->refresh();
                $movement->updateQuietly([
                    'quantity_before' => $qtyBefore,
                    'quantity_after' => $tool->available_quantity,
                    'approved_at' => now(),
                ]);
            }
        });
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'in' => 'Barang Masuk',
            'out' => 'Barang Keluar',
            'adjustment' => 'Penyesuaian',
            default => ucfirst($this->type),
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }
}

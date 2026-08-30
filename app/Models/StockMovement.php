<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Traits\LogsAuditActivity;

class StockMovement extends Model
{
    use HasFactory, LogsAuditActivity;

    protected $fillable = [
        'reference_number', 'component_id', 'component_category_id', 'mold_id', 'machine_id',
        'requested_by', 'approved_by', 'type', 'status', 'quantity',
        'quantity_before', 'quantity_after', 'condition', 'purpose',
        'operator_name', 'photo', 'notes', 'rejection_reason',
        'approved_at', 'source_po_id',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (StockMovement $movement) {
            if (empty($movement->reference_number)) {
                $prefix = match($movement->type) {
                    'in' => 'IN',
                    'out' => 'OUT',
                    'return' => 'RET',
                    'adjustment' => 'ADJ',
                    'opname' => 'OPN',
                    default => 'TRX',
                };
                $movement->reference_number = $prefix . '-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
            }
        });

        static::created(function (StockMovement $movement) {
            if ($movement->status === 'approved') {
                $component = $movement->component;
                if ($component) {
                    $qtyBefore = $component->stock;

                    match($movement->type) {
                        'in', 'return' => $component->increment('stock', $movement->quantity),
                        'out' => $component->decrement('stock', $movement->quantity),
                        'adjustment' => $component->update(['stock' => $movement->quantity]),
                        default => null,
                    };

                    $component->refresh();
                    $movement->updateQuietly([
                        'quantity_before' => $qtyBefore,
                        'quantity_after' => $component->stock,
                        'approved_at' => $movement->approved_at ?? now(),
                    ]);
                }
            }
        });

        static::updated(function (StockMovement $movement) {
            if ($movement->wasChanged('status') && $movement->status === 'approved') {
                $component = $movement->component;
                $qtyBefore = $component->stock;

                match($movement->type) {
                    'in', 'return' => $component->increment('stock', $movement->quantity),
                    'out' => $component->decrement('stock', $movement->quantity),
                    'adjustment' => $component->update(['stock' => $movement->quantity]),
                    default => null,
                };

                $component->refresh();
                $movement->updateQuietly([
                    'quantity_before' => $qtyBefore,
                    'quantity_after' => $component->stock,
                    'approved_at' => now(),
                ]);
            }
        });
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    public function componentCategory(): BelongsTo
    {
        return $this->belongsTo(ComponentCategory::class);
    }

    public function mold(): BelongsTo
    {
        return $this->belongsTo(Mold::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function sourcePo(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'source_po_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'in' => 'Barang Masuk',
            'out' => 'Barang Keluar',
            'return' => 'Return',
            'adjustment' => 'Penyesuaian',
            'opname' => 'Stock Opname',
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

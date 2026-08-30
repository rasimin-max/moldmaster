<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\LogsAuditActivity;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes, LogsAuditActivity;

    protected $fillable = [
        'po_number', 'vendor_id', 'created_by', 'approved_by', 'status',
        'po_date', 'expected_arrival_date', 'actual_arrival_date',
        'total_amount', 'currency', 'payment_terms', 'shipping_method',
        'invoice_number', 'invoice_file', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'po_date' => 'date',
            'expected_arrival_date' => 'date',
            'actual_arrival_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PurchaseOrder $po) {
            if (empty($po->po_number)) {
                $year = now()->format('Y');
                $count = static::whereYear('created_at', $year)->count() + 1;
                $po->po_number = 'PO-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PoItem::class, 'purchase_order_id');
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'sent' => 'info',
            'ordered' => 'warning',
            'partial' => 'warning',
            'arrived' => 'success',
            'closed' => 'success',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    public function recalculateTotal(): void
    {
        $this->update(['total_amount' => $this->items()->sum('subtotal')]);
    }
}

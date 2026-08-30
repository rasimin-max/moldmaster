<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\LogsAuditActivity;

class Component extends Model
{
    use HasFactory, SoftDeletes, LogsAuditActivity;

    protected $fillable = [
        'code', 'qr_code', 'name', 'category_id', 'material_type_id', 'machining_type_id',
        'mold_id', 'vendor_id',
        'material', 'size_spec', 'rack_location', 'stock', 'required_qty', 'stock_minimum',
        'stock_reserved', 'unit_price', 'unit', 'shot_count', 'shot_life',
        'photo', 'qr_image', 'status', 'description', 'heat_treatment', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Component $component) {
            if (empty($component->code)) {
                $component->code = 'COMP-' . strtoupper(Str::random(6));
            }
            if (empty($component->qr_code)) {
                $component->qr_code = 'QR-' . strtoupper(Str::random(10));
            }
        });
    }

    // Relations
    public function category(): BelongsTo
    {
        return $this->belongsTo(ComponentCategory::class, 'category_id');
    }

    public function materialType(): BelongsTo
    {
        return $this->belongsTo(MaterialType::class);
    }

    public function machiningType(): BelongsTo
    {
        return $this->belongsTo(MachiningType::class);
    }

    public function mold(): BelongsTo
    {
        return $this->belongsTo(Mold::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function poItems(): HasMany
    {
        return $this->hasMany(PoItem::class);
    }

    // Computed
    public function getAvailableStockAttribute(): int
    {
        return $this->stock - $this->stock_reserved;
    }

    public function getTakenQtyAttribute(): int
    {
        return $this->stockMovements
            ->where('type', 'out')
            ->where('status', 'approved')
            ->sum('quantity');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= $this->stock_minimum;
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'ready' => 'success',
            'in_use' => 'warning',
            'pending_arrival' => 'info',
            'maintenance' => 'danger',
            'retired' => 'gray',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'ready' => 'Ready',
            'in_use' => 'Dipakai',
            'pending_arrival' => 'Belum Datang',
            'maintenance' => 'Maintenance',
            'retired' => 'Pensiunkan',
            default => ucfirst($this->status),
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoItem extends Model
{
    protected $table = 'po_items';

    protected $fillable = [
        'purchase_order_id', 'component_id', 'qty_ordered', 'qty_received',
        'unit_price', 'subtotal', 'unit', 'specifications', 'photo',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    protected static function booted(): void
    {
        static::saving(function (PoItem $item) {
            $item->subtotal = $item->qty_ordered * $item->unit_price;
        });

        static::saved(function (PoItem $item) {
            $item->purchaseOrder->recalculateTotal();
        });
    }
}

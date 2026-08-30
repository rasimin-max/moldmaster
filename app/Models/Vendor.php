<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAuditActivity;

class Vendor extends Model
{
    use HasFactory, SoftDeletes, LogsAuditActivity;

    protected $fillable = [
        'code', 'name', 'address', 'pic_name', 'phone', 'email',
        'lead_time_days', 'bank_name', 'bank_account', 'status', 'notes', 'photo',
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(Component::class);
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'success',
            'inactive' => 'danger',
            default => 'gray',
        };
    }
}

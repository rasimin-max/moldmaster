<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\LogsAuditActivity;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, LogsAuditActivity;

    protected $fillable = [
        'name',
        'employee_id',
        'email',
        'password',
        'phone',
        'area',
        'avatar',
        'is_active',
        'hourly_rate',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    // Relations
    public function stockMovementsRequested(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'requested_by');
    }

    public function stockMovementsApproved(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'approved_by');
    }

    public function toolLoans(): HasMany
    {
        return $this->hasMany(ToolLoan::class, 'borrower_id');
    }

    public function maintenancesReported(): HasMany
    {
        return $this->hasMany(Maintenance::class, 'reported_by');
    }

    public function maintenancesApproved(): HasMany
    {
        return $this->hasMany(Maintenance::class, 'approved_by');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'created_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function sagyoNippos(): HasMany
    {
        return $this->hasMany(SagyoNippo::class, 'user_id');
    }

    // Helpers
    public function getRoleNameAttribute(): string
    {
        return $this->roles->first()?->name ?? 'No Role';
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->avatar) {
            if (str_starts_with($this->avatar, 'http')) {
                return $this->avatar;
            }
            try {
                return Storage::disk('cloudinary')->url($this->avatar);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsAuditActivity;

class Project extends Model
{
    use HasFactory, SoftDeletes, LogsAuditActivity;

    protected $fillable = [
        'code', 'name', 'customer', 'start_date', 'end_date', 'budget', 'status', 'description',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'budget' => 'decimal:2',
        ];
    }

    public function molds(): HasMany
    {
        return $this->hasMany(Mold::class);
    }

    public function phases(): HasMany
    {
        return $this->hasMany(ProjectPhase::class);
    }
}

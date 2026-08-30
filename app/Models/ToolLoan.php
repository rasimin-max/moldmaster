<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Traits\LogsAuditActivity;

class ToolLoan extends Model
{
    use HasFactory, LogsAuditActivity;

    protected $fillable = [
        'loan_number', 'tool_id', 'borrower_id', 'approved_by', 'quantity', 'status',
        'purpose', 'planned_return_date', 'borrowed_at', 'returned_at',
        'condition_borrowed', 'condition_returned', 'notes', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'planned_return_date' => 'date',
            'borrowed_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ToolLoan $loan) {
            if (empty($loan->loan_number)) {
                $loan->loan_number = 'LOAN-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
            }
        });

        static::created(function (ToolLoan $loan) {
            if ($loan->status === 'borrowed') {
                $loan->tool->decrement('available_quantity', $loan->quantity);
            }
        });

        static::updated(function (ToolLoan $loan) {
            if ($loan->wasChanged('status')) {
                $tool = $loan->tool;
                match($loan->status) {
                    'borrowed' => $tool->decrement('available_quantity', $loan->quantity),
                    'returned' => $tool->increment('available_quantity', $loan->quantity),
                    default => null,
                };
            }
        });
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'borrower_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'borrowed' => 'primary',
            'returned' => 'success',
            'rejected' => 'danger',
            'overdue' => 'danger',
            default => 'gray',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'borrowed'
            && $this->planned_return_date
            && $this->planned_return_date->isPast();
    }
}

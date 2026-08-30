<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Improvement extends Model
{
    protected $fillable = [
        'title',
        'description',
        'reporter_name',
        'user_id',
        'status',
        'photo_before',
        'photo_after',
        'cost_effect',
        'implementation_date',
        'cost_investment',
    ];

    protected $casts = [
        'implementation_date' => 'date',
        'cost_effect' => 'decimal:2',
        'cost_investment' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

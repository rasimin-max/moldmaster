<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineSchedule extends Model
{
    protected $fillable = [
        'machine_id',
        'user_id',
        'project_id',
        'component_id',
        'operation_type',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function component()
    {
        return $this->belongsTo(Component::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

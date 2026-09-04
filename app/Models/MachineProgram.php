<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'mold_id', 'component_id', 'machine_id', 'programmer',
        'name', 'description', 'estimated_time', 'actual_time',
        'r_f', 'b', 'tool_no', 'tool_name', 'tool_dia', 'tool_r',
        'tool_length_total', 'tool_length_eff', 'tool_num', 'holder',
        'ps_thick', 'rpm', 'feed', 'doc', 'setting', 'barcode'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function mold()
    {
        return $this->belongsTo(Mold::class);
    }

    public function component()
    {
        return $this->belongsTo(Component::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    protected static function booted(): void
    {
        static::created(function (MachineProgram $program) {
            $time = null;
            if ($program->estimated_time) {
                $time = str_replace(',', '.', $program->estimated_time);
            }

            if ($program->machine_id) {
                \App\Models\MachineOperationRecord::create([
                    'machine_id' => $program->machine_id,
                    'project_id' => $program->project_id,
                    'mold_id' => $program->mold_id,
                    'component_id' => $program->component_id,
                    'machine_program_id' => $program->id,
                    'status' => 'plan_job',
                    'operation_type' => 'production',
                    'planned_duration_minutes' => $time,
                    // start_time is nullable now
                ]);
            }
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SagyoNippoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sagyo_nippo_id',
        'type',
        'project_id',
        'mold_id',
        'job_code_id',
        'part_code_id',
        'hours',
        'notes',
    ];

    public function sagyoNippo()
    {
        return $this->belongsTo(SagyoNippo::class);
    }

    public function sagyoType()
    {
        return $this->belongsTo(SagyoType::class, 'type');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function mold()
    {
        return $this->belongsTo(Mold::class);
    }

    public function jobCode()
    {
        return $this->belongsTo(JobCode::class);
    }

    public function partCode()
    {
        return $this->belongsTo(PartCode::class);
    }

    protected static function booted()
    {
        $updateTotalHours = function ($item) {
            if ($item->sagyoNippo) {
                $total = $item->sagyoNippo->items()->sum('hours');
                $item->sagyoNippo->update(['total_hours' => $total]);
            }
        };

        static::saved($updateTotalHours);
        static::deleted($updateTotalHours);
    }
}

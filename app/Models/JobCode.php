<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobCode extends Model
{
    use HasFactory;

    protected $fillable = ['item', 'code', 'rate'];

    public function sagyoNippos()
    {
        return $this->hasMany(SagyoNippo::class);
    }
}

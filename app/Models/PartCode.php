<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartCode extends Model
{
    use HasFactory;

    protected $fillable = ['item', 'code'];

    public function sagyoNippos()
    {
        return $this->hasMany(SagyoNippo::class);
    }
}

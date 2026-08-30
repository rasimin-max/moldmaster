<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPriority extends Model
{
    // Point to the database view
    protected $table = 'order_priorities';
    
    // The view doesn't have a primary key, but Filament needs one. 
    // We generated a pseudo primary key `id` in the view (e.g. C-1, T-1).
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    // The view is read-only
    public $timestamps = false;
    protected $guarded = [];

    // Accessors for styling in Filament
    public function getBadgeColorAttribute()
    {
        return match($this->type) {
            'component' => 'warning',
            'tool' => 'info',
            'project_component' => 'danger',
            default => 'gray',
        };
    }
}

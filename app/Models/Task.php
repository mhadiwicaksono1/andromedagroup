<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'order_index',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
        'order_index' => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}

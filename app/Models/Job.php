<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'cs_id',
        'technician_id',
        'status',
        'current_step',
        'feedback'
    ];

    // Relasi ke CS (User yang membuat tugas)
    public function cs()
    {
        return $this->belongsTo(User::class, 'cs_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function trackers()
    {
        return $this->hasMany(JobTracker::class);
    }
}
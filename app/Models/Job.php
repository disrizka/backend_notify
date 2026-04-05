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

    public function comments()
    {
        return $this->hasMany(JobComment::class)->with('user')->latest();
    }
}
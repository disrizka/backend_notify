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
        'feedback',

        'client_name',
        'location',
        'latitude',
        'longitude',


        'start_time',
        'end_time',

   
        'accepted_at',
        'completed_at',
        'actual_duration',
        'completion_reason',
    ];

    protected $casts = [
        'start_time'   => 'datetime',
        'end_time'     => 'datetime',
        'accepted_at'  => 'datetime',
        'completed_at' => 'datetime',
        'latitude'     => 'float',
        'longitude'    => 'float',
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

  
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->end_time || !$this->accepted_at) return false;
        $reference = $this->completed_at ?? now();
        return $reference->isAfter($this->end_time);
    }


    public function getActualDurationLabelAttribute(): ?string
    {
        if (!$this->actual_duration) return null;
        $h = intdiv($this->actual_duration, 60);
        $m = $this->actual_duration % 60;
        return $h > 0 ? "{$h}j {$m}m" : "{$m}m";
    }
}
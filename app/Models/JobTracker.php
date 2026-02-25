<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobTracker extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'step_number',
        'description_value',
        'photo_path',
        'video_path'
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    // Agar bisa mengisi data lewat request->all()
    protected $fillable = [
        'name', 'step_1', 'step_2', 'step_3', 'step_4',
        'req_desc_1', 'req_photo_1', 'req_video_1',
        'req_desc_2', 'req_photo_2', 'req_video_2',
        'req_desc_3', 'req_photo_3', 'req_video_3',
        'req_desc_4', 'req_photo_4', 'req_video_4',
    ];

    public function users()
    {
        // Hubungan: Satu divisi punya banyak (hasMany) User
        return $this->hasMany(User::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    // Tambahkan baris ini Rizka:
    protected $fillable = [
        'holiday_date',
        'name',
    ];
}
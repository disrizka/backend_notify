<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // <--- Tambahkan ini untuk memastikan

class Presence extends Model
{
    use HasFactory;

    protected $fillable = [
    'user_id', 'date', 'category', 'check_in', 'check_out', 
    'lat_in', 'lng_in', 'lat_out', 'lng_out', 
    'photo_in', 'photo_out', 'notes', 'notes_out', 'is_approved'
];

    // PASTIKAN NAMA FUNGSINYA 'user' (huruf kecil semua)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id'); // <--- Tambahkan 'user_id' secara eksplisit
    }
}
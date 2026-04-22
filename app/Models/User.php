<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'division_id', 
        'role',       
        'is_default_password',
        'avatar',
        'requires_location', 
        'radius_meters',
        'fcm_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_default_password' => 'boolean',
            'requires_location' => 'boolean',
        ];
    }

    /**
     * Relasi ke Tabel Division
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function jobsAsTechnician()
    {
        return $this->hasMany(Job::class, 'technician_id');
    }

    public function jobsAsCS()
    {
        return $this->hasMany(Job::class, 'cs_id');
    }
}
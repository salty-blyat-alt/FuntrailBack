<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens; 


class User extends Model
{
    use Billable, HasApiTokens, HasFactory, Notifiable; 
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',  
        'email', 
        'password', 
        'user_type', 
        'province_id', 
        'balance', 
        'phone_number', 
        'profile_img'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = ['password', 'remember_token'];

    public function hotels()
    {
        return $this->hasMany(Hotel::class);
    }


    public function restaurants()
    {
        return $this->hasMany(Restaurant::class);
    }


    public function provinces()
    {
        return $this->belongsTo(Province::class, 'province_id', 'id');    
    }

    public function bookings()
    {
        return $this->belongsTo(Booking::class);    
    }
    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime:H:i:s',
    ];
}

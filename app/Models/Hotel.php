<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'hotels';

    protected $fillable = [
        'name',
        'user_id', 
        'province_id',
        'address',
        'description',
        'room_available',
        'phone_number',
        'image',
        'open_at',
        'close_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'open_at' => 'datetime:H:i:s',
        'close_at' => 'datetime:H:i:s',
    ];

    /**
     * Get the bookings for the hotel.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'id');
    }
    
    public function hotelDetails()
    {
        return $this->hasMany(HotelDetail::class);
    }
    
}

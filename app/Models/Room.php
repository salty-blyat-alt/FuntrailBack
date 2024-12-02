<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;
    protected $dates = ['deleted_at'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'hotel_id',
        'room_type',
        'price_per_night', 
        'status', 
        'img', 
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'hotel_id' => 'int',
        'room_type' => 'string',
        'status' => 'string',
        'price_per_night' => 'float',
        'img' => 'string'
    ];

    /**
     * Get the products for the restaurant.
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    } 
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'room_id');
    }
  
}

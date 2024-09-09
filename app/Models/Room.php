<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'hotel_id',
        'room_type',
        'price_per_night' 
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'hotel_id' => 'int',
        'room_type' => 'string',
        'price_per_night' => 'float'
    ];

    /**
     * Get the products for the restaurant.
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    } 
}

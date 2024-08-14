<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelDetail extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['room_type_id', 'hotel_id','image', 'is_available'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_available' => 'boolean',  
    ];

    /**
     * Get the hotel that owns the detail.
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the room type associated with the detail.
     */
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}

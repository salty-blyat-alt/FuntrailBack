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
        'price',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'hotel_id' => 'int',
        'room_type' => 'string',
        'price' => 'numeric',
        'status' => 'string',
    ];

    /**
     * Get the products for the restaurant.
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    } 
}

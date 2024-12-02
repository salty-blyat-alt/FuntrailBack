<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hotel extends Model
{

    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $dates = ['deleted_at'];
    protected $table = 'hotels';

    protected $fillable = [
        'name',
        'user_id',
        'province_id',
        'address',
        'description',
        'thumbnail',
        'images',
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
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'id');
    }
  


}

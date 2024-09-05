<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'commission_rate',
        'total_commision',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id' => 'int',
        'commission_rate' => 'int',
        'total_commision' => 'float',
    ];

    /**
     * Get the products for the restaurant.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

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
        'payment_type',
        'total_payment',
        'commission_rate',
        'total_commission',
        'booking_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id'           => 'int',
        'commission_rate'   => 'int',
        'total_commission'  => 'decimal',
        'payment_type'      => 'string',
        'total_payment'     => 'decimal',
    ];

    /**
     * Get the products for the restaurant.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

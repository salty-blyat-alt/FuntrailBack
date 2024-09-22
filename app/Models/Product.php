<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
        /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'is_instock',
        'price',
        'restaurant_id',
        'image',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_instock' => 'boolean', 
        'price' => 'decimal:2',  
    ];

    /**
     * Get the restaurant that owns the product.
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Get the order details for the product.
     */
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
}

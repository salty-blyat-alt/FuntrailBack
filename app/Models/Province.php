<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    /**
     * Get the hotels for the province.
     */
    public function hotels()
    {
        return $this->hasMany(Hotel::class, 'province_id');
    }

    /**
     * Get the restaurants for the province.
     */
    public function restaurants()
    {
        return $this->hasMany(Restaurant::class, 'province_id');
    }

    /**
     * Get the users for the province.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'province_id');
    }
}

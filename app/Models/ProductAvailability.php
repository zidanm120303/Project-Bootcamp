<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAvailability extends Model
{
    protected $fillable = [
        'product_id', 'day_of_week', 'start_time', 'end_time', 'capacity', 'is_available',
    ];

    protected $casts = ['is_available' => 'boolean'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

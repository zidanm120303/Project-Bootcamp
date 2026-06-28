<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingItem extends Model
{
    protected $fillable = [
        'booking_id', 'product_id', 'product_unit_id', 'quantity', 'price_per_unit', 'price_unit',
        'rental_days', 'start_at', 'end_at', 'subtotal', 'item_status',
    ];

    protected $casts = [
        'start_at' => 'datetime', 'end_at' => 'datetime', 'price_per_unit' => 'decimal:2', 'subtotal' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }
}

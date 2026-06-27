<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    protected $fillable = [
        'product_id', 'unit_code', 'serial_number', 'condition_status', 'availability_status', 'notes',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

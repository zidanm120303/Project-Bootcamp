<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBlackoutDate extends Model
{
    protected $fillable = ['product_id', 'start_at', 'end_at', 'reason'];

    protected $casts = ['start_at' => 'datetime', 'end_at' => 'datetime'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

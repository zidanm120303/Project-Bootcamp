<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'booking_id', 'payment_code', 'method', 'amount', 'proof_file', 'status',
        'paid_at', 'verified_by', 'verified_at', 'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2', 'paid_at' => 'datetime', 'verified_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}

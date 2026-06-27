<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'booking_code', 'customer_id', 'partner_id', 'booking_type', 'start_at', 'end_at',
        'subtotal_amount', 'deposit_amount', 'platform_fee', 'total_amount', 'payment_status',
        'status', 'customer_notes', 'partner_notes', 'cancelled_by', 'cancelled_reason',
    ];

    protected $casts = [
        'start_at' => 'datetime', 'end_at' => 'datetime', 'subtotal_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2', 'platform_fee' => 'decimal:2', 'total_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function partner()
    {
        return $this->belongsTo(PartnerProfile::class, 'partner_id');
    }

    public function items()
    {
        return $this->hasMany(BookingItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class);
    }
}

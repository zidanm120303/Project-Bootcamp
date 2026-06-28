<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    public const ACTIVE_RENTAL_STATUSES = ['pending', 'confirmed', 'ready_pickup', 'ongoing'];

    protected $fillable = [
        'booking_code', 'customer_id', 'partner_id', 'booking_type', 'pickup_method', 'pickup_note',
        'customer_name', 'customer_phone', 'customer_email', 'customer_address',
        'identity_number', 'identity_file', 'start_at', 'end_at',
        'subtotal_amount', 'deposit_amount', 'platform_fee', 'total_amount', 'payment_status',
        'status', 'confirmed_at', 'ready_pickup_at', 'picked_up_at', 'returned_at',
        'completed_at', 'cancelled_at', 'customer_notes', 'partner_notes',
        'return_condition', 'late_fee', 'damage_fee', 'deposit_status',
        'deposit_refunded_at', 'cancelled_by', 'cancelled_reason',
    ];

    protected $casts = [
        'start_at' => 'datetime', 'end_at' => 'datetime', 'subtotal_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2', 'platform_fee' => 'decimal:2', 'total_amount' => 'decimal:2',
        'late_fee' => 'decimal:2', 'damage_fee' => 'decimal:2',
        'confirmed_at' => 'datetime', 'ready_pickup_at' => 'datetime',
        'picked_up_at' => 'datetime', 'returned_at' => 'datetime',
        'completed_at' => 'datetime', 'cancelled_at' => 'datetime',
        'deposit_refunded_at' => 'datetime',
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

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->payments()->where('status', 'paid')->sum('amount');
    }

    public function getOutstandingAmountAttribute(): float
    {
        return max(0, (float) $this->total_amount - $this->paid_amount);
    }
}

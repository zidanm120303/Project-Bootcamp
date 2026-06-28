<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'business_name', 'business_type', 'owner_name', 'phone',
        'business_email', 'tax_number', 'bank_name', 'bank_account_number',
        'bank_account_holder', 'operational_hours', 'pickup_note',
        'address', 'city', 'province', 'postal_code', 'description', 'logo_path',
        'banner_path', 'verification_status', 'admin_notes', 'trusted_score', 'is_trusted', 'verified_at',
    ];

    protected $casts = ['is_trusted' => 'boolean', 'verified_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'partner_id');
    }

    public function documents()
    {
        return $this->hasMany(PartnerDocument::class, 'partner_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'partner_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'partner_id');
    }
}

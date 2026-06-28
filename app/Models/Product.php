<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'partner_id', 'category_id', 'name', 'brand', 'model', 'camera_type',
        'sensor_type', 'resolution_mp', 'video_resolution', 'lens_mount',
        'condition_label', 'included_accessories', 'rental_terms', 'slug',
        'product_type', 'description', 'price', 'security_deposit', 'replacement_value',
        'price_unit', 'stock_total', 'min_rent_duration', 'max_rent_duration',
        'location_city', 'location_address', 'status', 'admin_notes', 'is_featured',
        'average_rating', 'total_reviews',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'replacement_value' => 'decimal:2',
        'resolution_mp' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'is_featured' => 'boolean',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('status', 'active')
            ->whereHas('partner', fn ($partner) => $partner->where('verification_status', 'verified'));
    }

    public function partner()
    {
        return $this->belongsTo(PartnerProfile::class, 'partner_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function units()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function availabilities()
    {
        return $this->hasMany(ProductAvailability::class);
    }

    public function blackoutDates()
    {
        return $this->hasMany(ProductBlackoutDate::class);
    }

    public function bookingItems()
    {
        return $this->hasMany(BookingItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function hasActiveBookings(): bool
    {
        return $this->bookingItems()
            ->whereHas('booking', fn ($query) => $query->whereIn('status', Booking::ACTIVE_RENTAL_STATUSES))
            ->exists();
    }

    public function hasBookingHistory(): bool
    {
        return $this->bookingItems()->exists();
    }

    public function getImageUrlAttribute(): string
    {
        $path = optional($this->primaryImage)->image_path ?? optional($this->images->first())->image_path;
        if (! $path) {
            return 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=1200&q=80';
        }

        return str_starts_with($path, 'http') ? $path : asset('storage/'.$path);
    }
}

<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function update(User $user, Product $product): bool
    {
        return $user->role === 'mitra' && $user->partnerProfile?->id === $product->partner_id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->update($user, $product) && ! $product->bookingItems()->whereHas('booking', fn ($q) => $q->whereIn('status', ['confirmed', 'waiting_payment', 'paid', 'prepared', 'ongoing']))->exists();
    }
}

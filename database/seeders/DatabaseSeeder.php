<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Dispute;
use App\Models\PartnerProfile;
use App\Models\Product;
use App\Models\Review;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin Rentra', 'email' => 'admin@rentra.test', 'phone' => '081100000001',
            'password' => Hash::make('password'), 'role' => 'admin', 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $mitraUser = User::create([
            'name' => 'Budi Santoso', 'email' => 'mitra@rentra.test', 'phone' => '081100000002',
            'password' => Hash::make('password'), 'role' => 'mitra', 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $eventUser = User::create([
            'name' => 'Nadia Putri', 'email' => 'event@rentra.test', 'phone' => '081100000004',
            'password' => Hash::make('password'), 'role' => 'mitra', 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $pendingUser = User::create([
            'name' => 'Raka Pratama', 'email' => 'pending@rentra.test', 'phone' => '081100000005',
            'password' => Hash::make('password'), 'role' => 'mitra', 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $customer = User::create([
            'name' => 'Ayu Lestari', 'email' => 'customer@rentra.test', 'phone' => '081100000003',
            'password' => Hash::make('password'), 'role' => 'customer', 'status' => 'active', 'email_verified_at' => now(),
        ]);

        $lensRent = PartnerProfile::create([
            'user_id' => $mitraUser->id, 'business_name' => 'LensRent Jakarta', 'business_type' => 'Rental Kamera & Audio',
            'owner_name' => 'Budi Santoso', 'phone' => '081100000002', 'address' => 'Jl. Kemang Raya No. 21',
            'city' => 'Jakarta Selatan', 'province' => 'DKI Jakarta',
            'description' => 'Perlengkapan produksi profesional yang selalu dirawat dan siap dipakai.',
            'verification_status' => 'verified', 'trusted_score' => 92, 'is_trusted' => true, 'verified_at' => now()->subYear(),
        ]);
        $kitaEvent = PartnerProfile::create([
            'user_id' => $eventUser->id, 'business_name' => 'Kita Event Solution', 'business_type' => 'Event & Dekorasi',
            'owner_name' => 'Nadia Putri', 'phone' => '081100000004', 'address' => 'Jl. Margonda Raya No. 88',
            'city' => 'Depok', 'province' => 'Jawa Barat',
            'description' => 'Partner acara untuk dekorasi, catering, dan kebutuhan teknis yang dapat diandalkan.',
            'verification_status' => 'verified', 'trusted_score' => 88, 'is_trusted' => true, 'verified_at' => now()->subMonths(8),
        ]);
        $pending = PartnerProfile::create([
            'user_id' => $pendingUser->id, 'business_name' => 'Cahaya Visual Studio', 'business_type' => 'Fotografi',
            'owner_name' => 'Raka Pratama', 'phone' => '081100000005', 'address' => 'Jl. Asia Afrika No. 7',
            'city' => 'Bandung', 'province' => 'Jawa Barat', 'description' => 'Studio foto dan dokumentasi acara.',
            'verification_status' => 'pending',
        ]);

        foreach ([$lensRent, $kitaEvent] as $partner) {
            foreach (['ktp', 'nib', 'rekening'] as $type) {
                $partner->documents()->create([
                    'document_type' => $type, 'document_number' => strtoupper($type).'-'.Str::random(8),
                    'file_path' => 'demo/'.$type.'.pdf', 'status' => 'approved', 'reviewed_by' => $admin->id, 'reviewed_at' => now(),
                ]);
            }
        }
        $pending->documents()->create([
            'document_type' => 'ktp', 'document_number' => 'KTP-DEMO-01',
            'file_path' => 'demo/ktp.pdf', 'status' => 'pending',
        ]);

        $categoryRows = [
            ['Kamera & Video', 'kamera-video', 'camera'],
            ['Sound System', 'sound-system', 'speaker'],
            ['Dekorasi Acara', 'dekorasi-acara', 'sparkles'],
            ['Tenda & Panggung', 'tenda-panggung', 'tent'],
            ['Kendaraan', 'kendaraan', 'truck'],
            ['Catering', 'catering', 'utensils'],
            ['Jasa Event', 'jasa-event', 'party'],
            ['Produk UMKM', 'produk-umkm', 'store'],
        ];
        $categories = [];
        foreach ($categoryRows as [$name, $slug, $icon]) {
            $categories[$slug] = Category::create(['name' => $name, 'slug' => $slug, 'icon' => $icon, 'status' => 'active']);
        }

        $productRows = [
            [$lensRent, 'sound-system', 'Paket Sound System 5000 Watt', 'rental', 1200000, 'day', 3, 'Jakarta Selatan', 4.8, 120, true, 'https://images.unsplash.com/photo-1524650359799-842906ca1c06?auto=format&fit=crop&w=1200&q=85'],
            [$lensRent, 'kamera-video', 'Kamera Sony A6400 Creator Kit', 'rental', 350000, 'day', 5, 'Depok', 4.9, 86, true, 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=1200&q=85'],
            [$kitaEvent, 'tenda-panggung', 'Tenda Dekorasi 6×12 Meter', 'rental', 2800000, 'day', 3, 'Tangerang', 4.9, 72, true, 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1200&q=85'],
            [$kitaEvent, 'dekorasi-acara', 'Paket Dekorasi Rustic Premium', 'service', 4500000, 'service', 2, 'Bekasi', 4.8, 53, true, 'https://images.unsplash.com/photo-1507501336603-6e31db2be093?auto=format&fit=crop&w=1200&q=85'],
            [$lensRent, 'jasa-event', 'Jasa Fotografer Event', 'service', 1500000, 'service', 4, 'Jakarta Pusat', 4.9, 118, false, 'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?auto=format&fit=crop&w=1200&q=85'],
            [$kitaEvent, 'catering', 'Jasa Catering Nusantara 100 Pax', 'service', 2300000, 'service', 5, 'Bogor', 4.7, 88, false, 'https://images.unsplash.com/photo-1555244162-803834f70033?auto=format&fit=crop&w=1200&q=85'],
            [$kitaEvent, 'jasa-event', 'Jasa MC Profesional', 'service', 800000, 'service', 3, 'Bandung', 4.8, 64, false, 'https://images.unsplash.com/photo-1475721027785-f74eccf877e2?auto=format&fit=crop&w=1200&q=85'],
            [$lensRent, 'kamera-video', 'LED Videotron P3 Indoor', 'rental', 6000000, 'day', 2, 'Surabaya', 4.9, 37, false, 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=1200&q=85'],
        ];
        $products = [];
        foreach ($productRows as $i => [$partner, $category, $name, $type, $price, $unit, $stock, $city, $rating, $reviews, $featured, $image]) {
            $product = Product::create([
                'partner_id' => $partner->id, 'category_id' => $categories[$category]->id,
                'name' => $name, 'slug' => Str::slug($name), 'product_type' => $type,
                'description' => $name.' berkualitas profesional untuk mendukung acara dan kebutuhan bisnis Anda. Paket terawat, transparan, dan didukung tim berpengalaman.',
                'price' => $price, 'price_unit' => $unit, 'stock_total' => $stock, 'min_rent_duration' => 1,
                'location_city' => $city, 'location_address' => 'Area layanan '.$city, 'status' => 'active',
                'is_featured' => $featured, 'average_rating' => $rating, 'total_reviews' => $reviews,
            ]);
            $product->images()->create(['image_path' => $image, 'is_primary' => true]);
            for ($day = 1; $day <= 6; $day++) {
                $product->availabilities()->create(['day_of_week' => $day, 'start_time' => '08:00', 'end_time' => '20:00', 'capacity' => $stock]);
            }
            $products[] = $product;
        }
        $draft = Product::create([
            'partner_id' => $pending->id, 'category_id' => $categories['kamera-video']->id,
            'name' => 'Paket Studio Foto Produk', 'slug' => 'paket-studio-foto-produk', 'product_type' => 'service',
            'description' => 'Paket foto produk lengkap untuk katalog dan sosial media UMKM.',
            'price' => 750000, 'price_unit' => 'service', 'stock_total' => 2, 'location_city' => 'Bandung', 'status' => 'pending_review',
        ]);
        $draft->images()->create(['image_path' => 'https://images.unsplash.com/photo-1452780212940-6f5c0d14d848?auto=format&fit=crop&w=1200&q=85', 'is_primary' => true]);

        $statuses = ['pending', 'waiting_payment', 'paid', 'prepared', 'ongoing', 'completed'];
        foreach ($statuses as $i => $status) {
            $product = $products[$i];
            $start = now()->addDays(3 + ($i * 3))->startOfDay();
            $end = $start->copy()->addDays(2);
            if ($status === 'completed') {
                $start = now()->subDays(8)->startOfDay();
                $end = now()->subDays(6)->startOfDay();
            }
            $booking = Booking::create([
                'booking_code' => 'RTR-2606-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id, 'partner_id' => $product->partner_id, 'booking_type' => $product->product_type,
                'start_at' => $start, 'end_at' => $end, 'subtotal_amount' => $product->price * 2,
                'platform_fee' => $product->price * .1, 'total_amount' => $product->price * 2.1,
                'payment_status' => in_array($status, ['paid', 'prepared', 'ongoing', 'completed']) ? 'paid' : 'unpaid',
                'status' => $status, 'customer_notes' => 'Mohon dipastikan semua perlengkapan siap digunakan.',
                'created_at' => now()->subDays(6 - $i),
            ]);
            $booking->items()->create([
                'product_id' => $product->id, 'qty' => 1, 'price' => $product->price, 'price_unit' => $product->price_unit,
                'duration' => 2, 'start_at' => $start, 'end_at' => $end, 'subtotal' => $product->price * 2,
                'item_status' => match ($status) {
                    'prepared' => 'prepared', 'ongoing' => 'ongoing', 'completed' => 'completed', default => 'booked'
                },
            ]);
            if (in_array($status, ['paid', 'prepared', 'ongoing', 'completed'])) {
                $booking->payments()->create([
                    'payment_code' => 'PAY-2606-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                    'method' => 'bank_transfer', 'amount' => $booking->total_amount, 'proof_file' => 'demo/payment.jpg',
                    'status' => 'paid', 'paid_at' => now()->subDays(4), 'verified_by' => $admin->id, 'verified_at' => now()->subDays(4),
                ]);
            }
        }

        $completed = Booking::where('status', 'completed')->first();
        Review::create([
            'booking_id' => $completed->id, 'customer_id' => $customer->id, 'partner_id' => $completed->partner_id,
            'product_id' => $completed->items()->first()->product_id, 'rating' => 5,
            'review_text' => 'Pelayanan cepat, alat bersih, dan semua berjalan tepat waktu.',
        ]);

        $disputed = Booking::create([
            'booking_code' => 'RTR-2606-0007', 'customer_id' => $customer->id, 'partner_id' => $lensRent->id,
            'booking_type' => 'rental', 'start_at' => now()->subDays(3), 'end_at' => now()->subDay(),
            'subtotal_amount' => 700000, 'platform_fee' => 35000, 'total_amount' => 735000,
            'payment_status' => 'paid', 'status' => 'disputed',
        ]);
        $disputed->items()->create([
            'product_id' => $products[1]->id, 'qty' => 1, 'price' => 350000, 'price_unit' => 'day',
            'duration' => 2, 'start_at' => now()->subDays(3), 'end_at' => now()->subDay(), 'subtotal' => 700000,
        ]);
        Dispute::create([
            'booking_id' => $disputed->id, 'reporter_id' => $customer->id, 'issue_type' => 'Keterlambatan',
            'description' => 'Pengambilan barang terlambat satu jam dari jadwal yang telah disepakati.', 'status' => 'open',
        ]);

        foreach ([
            'platform_fee_percent' => 5, 'trusted_min_score' => 85,
            'payment_due_hours' => 24, 'booking_auto_cancel_hours' => 24,
        ] as $key => $value) {
            SystemSetting::create(['key' => $key, 'value' => (string) $value, 'type' => 'number']);
        }
    }
}

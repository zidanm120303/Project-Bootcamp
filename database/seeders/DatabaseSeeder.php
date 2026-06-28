<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Category;
use App\Models\PartnerProfile;
use App\Models\Product;
use App\Models\Review;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'platform_fee_percent' => 5,
            'trusted_min_score' => 85,
            'payment_due_hours' => 24,
            'booking_auto_cancel_hours' => 24,
        ] as $key => $value) {
            SystemSetting::create(['key' => $key, 'value' => (string) $value, 'type' => 'number']);
        }

        $admin = $this->user('Admin RentalPro', 'admin@rentra.test', '081100000001', 'admin');
        $mitraUser = $this->user('Budi Santoso', 'mitra@rentra.test', '081100000002', 'mitra');
        $studioUser = $this->user('Nadia Prameswari', 'studio@rentra.test', '081100000004', 'mitra');
        $pendingUser = $this->user('Raka Pratama', 'pending@rentra.test', '081100000005', 'mitra');
        $customer = $this->user('Ayu Lestari', 'customer@rentra.test', '081100000003', 'customer');
        $customerTwo = $this->user('Dimas Mahendra', 'dimas@rentra.test', '081288880002', 'customer');
        Storage::put('user-identities/demo-ayu.pdf', '%PDF-1.4 Identitas profil demo Ayu Lestari');
        Storage::put('user-identities/demo-dimas.pdf', '%PDF-1.4 Identitas profil demo Dimas Mahendra');
        $customer->update([
            'date_of_birth' => '1998-08-17',
            'gender' => 'female',
            'profession' => 'Content Creator',
            'address' => 'Jl. Melati No. 12',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12560',
            'identity_type' => 'ktp',
            'identity_number' => '3174000000000001',
            'identity_file' => 'user-identities/demo-ayu.pdf',
            'emergency_contact_name' => 'Rina Lestari',
            'emergency_contact_phone' => '081299990001',
        ]);
        $customerTwo->update([
            'date_of_birth' => '1996-04-11',
            'gender' => 'male',
            'profession' => 'Videografer',
            'address' => 'Jl. Mawar No. 8',
            'city' => 'Depok',
            'province' => 'Jawa Barat',
            'postal_code' => '16424',
            'identity_type' => 'ktp',
            'identity_number' => '3276000000000002',
            'identity_file' => 'user-identities/demo-dimas.pdf',
            'emergency_contact_name' => 'Dewi Mahendra',
            'emergency_contact_phone' => '081299990002',
        ]);

        $lensaku = PartnerProfile::create([
            'user_id' => $mitraUser->id,
            'business_name' => 'Lensaku Camera Rental',
            'business_type' => 'Rental Kamera dan Perlengkapan Produksi',
            'owner_name' => 'Budi Santoso',
            'phone' => '081100000002',
            'business_email' => 'operasional@lensaku.test',
            'tax_number' => '09.123.456.7-012.000',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'Budi Santoso',
            'address' => 'Jl. Kemang Raya No. 21',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12730',
            'operational_hours' => 'Senin-Sabtu, 08.00-20.00 WIB',
            'pickup_note' => 'Tunjukkan kode booking dan identitas asli. Pemeriksaan kamera dilakukan bersama petugas sebelum dibawa.',
            'description' => 'Rental kamera profesional dengan unit terawat, sensor bersih, baterai sehat, dan dukungan teknis untuk produksi foto maupun video.',
            'verification_status' => 'verified',
            'trusted_score' => 94,
            'is_trusted' => true,
            'verified_at' => now()->subYear(),
        ]);
        $visualHub = PartnerProfile::create([
            'user_id' => $studioUser->id,
            'business_name' => 'VisualHub Camera',
            'business_type' => 'Rental Kamera, Drone, dan Lighting',
            'owner_name' => 'Nadia Prameswari',
            'phone' => '081100000004',
            'business_email' => 'halo@visualhub.test',
            'tax_number' => '09.987.654.3-411.000',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '1370012345678',
            'bank_account_holder' => 'Nadia Prameswari',
            'address' => 'Jl. Margonda Raya No. 88',
            'city' => 'Depok',
            'province' => 'Jawa Barat',
            'postal_code' => '16424',
            'operational_hours' => 'Senin-Minggu, 09.00-21.00 WIB',
            'pickup_note' => 'Konfirmasi kedatangan melalui WhatsApp minimal 30 menit sebelumnya.',
            'description' => 'Penyedia kamera, drone, lighting, dan audio produksi untuk kreator, rumah produksi, serta dokumentasi acara.',
            'verification_status' => 'verified',
            'trusted_score' => 90,
            'is_trusted' => true,
            'verified_at' => now()->subMonths(9),
        ]);
        $pendingPartner = PartnerProfile::create([
            'user_id' => $pendingUser->id,
            'business_name' => 'Cahaya Kamera Bandung',
            'business_type' => 'Rental Kamera dan Studio',
            'owner_name' => 'Raka Pratama',
            'phone' => '081100000005',
            'business_email' => 'raka@cahayakamera.test',
            'tax_number' => '09.555.444.3-423.000',
            'bank_name' => 'BRI',
            'bank_account_number' => '012301000987501',
            'bank_account_holder' => 'Raka Pratama',
            'address' => 'Jl. Asia Afrika No. 7',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40111',
            'operational_hours' => 'Senin-Jumat, 09.00-18.00 WIB',
            'pickup_note' => 'Bawa identitas asli dan lakukan pemeriksaan unit bersama petugas.',
            'description' => 'Rental kamera dan studio foto produk untuk UMKM Bandung.',
            'verification_status' => 'pending',
            'admin_notes' => 'Foto lokasi usaha belum tersedia dan NIB masih perlu diperiksa.',
        ]);

        $this->seedPartnerDocuments($lensaku, $admin, true);
        $this->seedPartnerDocuments($visualHub, $admin, true);
        $this->seedPartnerDocuments($pendingPartner, $admin, false);

        $categoryRows = [
            ['Mirrorless', 'mirrorless', 'camera'],
            ['DSLR', 'dslr', 'camera'],
            ['Cinema Camera', 'cinema-camera', 'video'],
            ['Lensa', 'lensa', 'search'],
            ['Lighting', 'lighting', 'sparkles'],
            ['Audio', 'audio', 'speaker'],
            ['Drone & Action Cam', 'drone-action', 'video'],
            ['Stabilizer', 'stabilizer', 'shield'],
        ];
        $categories = [];
        foreach ($categoryRows as [$name, $slug, $icon]) {
            $categories[$slug] = Category::create([
                'name' => $name,
                'slug' => $slug,
                'icon' => $icon,
                'description' => "Peralatan {$name} untuk kebutuhan produksi foto dan video.",
                'status' => 'active',
            ]);
        }

        $productRows = [
            [$lensaku, 'mirrorless', 'Sony A7 IV Full Frame Creator Kit', 'Sony', 'A7 IV', 'Mirrorless', 'Full Frame CMOS', 33, '4K 60fps 10-bit', 'Sony E-Mount', 450000, 200000, 39000000, 4, 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=1200&q=85'],
            [$lensaku, 'mirrorless', 'Canon EOS R6 Mark II Wedding Kit', 'Canon', 'EOS R6 Mark II', 'Mirrorless', 'Full Frame CMOS', 24.2, '4K 60fps oversampled', 'Canon RF', 475000, 200000, 42000000, 3, 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?auto=format&fit=crop&w=1200&q=85'],
            [$lensaku, 'mirrorless', 'Fujifilm X-T5 Street Photography Kit', 'Fujifilm', 'X-T5', 'Mirrorless', 'APS-C X-Trans CMOS 5 HR', 40.2, '6.2K 30fps', 'Fujifilm X', 375000, 150000, 30000000, 3, 'https://images.unsplash.com/photo-1606980707986-e087033609a1?auto=format&fit=crop&w=1200&q=85'],
            [$lensaku, 'cinema-camera', 'Sony FX3 Cinema Line Production Kit', 'Sony', 'FX3', 'Cinema Camera', 'Full Frame Exmor R CMOS', 12.1, '4K 120fps 10-bit', 'Sony E-Mount', 850000, 300000, 65000000, 2, 'https://images.unsplash.com/photo-1492619375914-88005aa9e8fb?auto=format&fit=crop&w=1200&q=85'],
            [$lensaku, 'lensa', 'Canon RF 70-200mm F2.8L IS USM', 'Canon', 'RF 70-200mm F2.8L', 'Lensa', null, null, null, 'Canon RF', 325000, 150000, 40000000, 3, 'https://images.unsplash.com/photo-1617005082133-548c4dd27f35?auto=format&fit=crop&w=1200&q=85'],
            [$lensaku, 'stabilizer', 'DJI RS 3 Pro Combo', 'DJI', 'RS 3 Pro', 'Stabilizer', null, null, null, null, 300000, 100000, 12000000, 3, 'https://images.unsplash.com/photo-1533488765986-dfa2a9939acd?auto=format&fit=crop&w=1200&q=85'],
            [$visualHub, 'lighting', 'Aputure LS 300D II Lighting Kit', 'Aputure', 'LS 300D II', 'Lighting', null, null, null, 'Bowens Mount', 275000, 100000, 14000000, 4, 'https://images.unsplash.com/photo-1520390138845-fd2d229dd553?auto=format&fit=crop&w=1200&q=85'],
            [$visualHub, 'drone-action', 'DJI Mini 4 Pro Fly More Combo', 'DJI', 'Mini 4 Pro', 'Drone', '1/1.3-inch CMOS', 48, '4K 100fps', null, 500000, 200000, 18000000, 2, 'https://images.unsplash.com/photo-1473968512647-3e447244af8f?auto=format&fit=crop&w=1200&q=85'],
            [$visualHub, 'drone-action', 'GoPro HERO12 Black Adventure Kit', 'GoPro', 'HERO12 Black', 'Action Camera', '1/1.9-inch CMOS', 27, '5.3K 60fps', null, 175000, 75000, 7500000, 5, 'https://images.unsplash.com/photo-1526336024174-e58f5cdd8e13?auto=format&fit=crop&w=1200&q=85'],
            [$visualHub, 'audio', 'Rode Wireless PRO Dual Mic Kit', 'Rode', 'Wireless PRO', 'Audio', null, null, '32-bit float recording', null, 200000, 75000, 6500000, 4, 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?auto=format&fit=crop&w=1200&q=85'],
        ];

        $products = [];
        foreach ($productRows as $index => $row) {
            [$partner, $category, $name, $brand, $model, $cameraType, $sensor, $megapixel, $video, $mount, $price, $deposit, $replacement, $stock, $image] = $row;
            $product = Product::create([
                'partner_id' => $partner->id,
                'category_id' => $categories[$category]->id,
                'name' => $name,
                'brand' => $brand,
                'model' => $model,
                'camera_type' => $cameraType,
                'sensor_type' => $sensor,
                'resolution_mp' => $megapixel,
                'video_resolution' => $video,
                'lens_mount' => $mount,
                'condition_label' => $index % 3 === 0 ? 'Seperti Baru' : 'Sangat Baik',
                'included_accessories' => $this->accessoriesFor($cameraType),
                'rental_terms' => "Identitas asli wajib ditunjukkan saat pengambilan.\nDilarang membawa unit ke luar negeri tanpa persetujuan tertulis.\nKerusakan atau kehilangan menjadi tanggung jawab penyewa.",
                'slug' => Str::slug($name),
                'product_type' => 'rental',
                'description' => "{$name} terawat dengan pemeriksaan fungsi, kebersihan sensor, baterai, dan kelengkapan sebelum setiap penyewaan.",
                'price' => $price,
                'security_deposit' => $deposit,
                'replacement_value' => $replacement,
                'price_unit' => 'day',
                'stock_total' => $stock,
                'min_rent_duration' => 1,
                'max_rent_duration' => 14,
                'location_city' => $partner->city,
                'location_address' => $partner->address,
                'status' => 'active',
                'is_featured' => $index < 4,
                'average_rating' => 4.7 + (($index % 3) / 10),
                'total_reviews' => 24 + ($index * 7),
            ]);
            $product->images()->create(['image_path' => $image, 'is_primary' => true]);
            for ($unit = 1; $unit <= $stock; $unit++) {
                $product->units()->create([
                    'unit_code' => 'CAM-'.str_pad((string) $product->id, 3, '0', STR_PAD_LEFT).'-'.str_pad((string) $unit, 3, '0', STR_PAD_LEFT),
                    'serial_number' => strtoupper(substr($brand, 0, 3)).'-'.now()->format('y').str_pad((string) ($product->id * 10 + $unit), 5, '0', STR_PAD_LEFT),
                    'condition_status' => 'good',
                    'availability_status' => 'available',
                    'notes' => 'Unit lolos pemeriksaan fungsi dan kebersihan.',
                ]);
            }
            for ($day = 1; $day <= 7; $day++) {
                $product->availabilities()->create([
                    'day_of_week' => $day,
                    'start_time' => '08:00',
                    'end_time' => '20:00',
                    'capacity' => $stock,
                ]);
            }
            $products[] = $product;
        }

        $pendingProduct = Product::create([
            'partner_id' => $pendingPartner->id,
            'category_id' => $categories['dslr']->id,
            'name' => 'Nikon D850 Commercial Photography Kit',
            'brand' => 'Nikon',
            'model' => 'D850',
            'camera_type' => 'DSLR',
            'sensor_type' => 'Full Frame BSI CMOS',
            'resolution_mp' => 45.7,
            'video_resolution' => '4K 30fps',
            'lens_mount' => 'Nikon F',
            'condition_label' => 'Sangat Baik',
            'included_accessories' => "Body Nikon D850\nLensa 24-70mm f/2.8\n2 baterai\nCharger\nTas kamera",
            'rental_terms' => 'Identitas asli wajib ditunjukkan dan penggunaan di luar Bandung harus dikonfirmasi.',
            'slug' => 'nikon-d850-commercial-photography-kit',
            'product_type' => 'rental',
            'description' => 'Kamera DSLR resolusi tinggi untuk foto produk, fashion, dan kebutuhan komersial.',
            'price' => 425000,
            'security_deposit' => 175000,
            'replacement_value' => 38000000,
            'price_unit' => 'day',
            'stock_total' => 2,
            'min_rent_duration' => 1,
            'location_city' => 'Bandung',
            'location_address' => $pendingPartner->address,
            'status' => 'pending_review',
        ]);
        $pendingProduct->images()->create(['image_path' => 'https://images.unsplash.com/photo-1512790182412-b19e6d62bc39?auto=format&fit=crop&w=1200&q=85', 'is_primary' => true]);

        Storage::put('customer-identities/demo-ayu.pdf', '%PDF-1.4 Identitas demo Ayu Lestari');
        Storage::put('customer-identities/demo-dimas.pdf', '%PDF-1.4 Identitas demo Dimas Mahendra');

        $bookingRows = [
            ['pending', $products[0], $customer, 1, 2, 10],
            ['confirmed', $products[1], $customer, 1, 2, 9],
            ['confirmed', $products[2], $customerTwo, 2, 3, 8],
            ['ready_pickup', $products[3], $customer, 1, 2, 7],
            ['ongoing', $products[4], $customerTwo, 1, 3, 6],
            ['returned', $products[5], $customer, 1, 2, 5],
            ['returned', $products[0], $customerTwo, 1, 2, 4],
            ['completed', $products[1], $customer, 1, 2, 12],
            ['cancelled', $products[2], $customerTwo, 1, 2, 3],
        ];

        $bookings = [];
        foreach ($bookingRows as $index => [$status, $product, $bookingCustomer, $quantity, $days, $offset]) {
            $bookings[] = $this->createBooking(
                $admin,
                $bookingCustomer,
                $product,
                $status,
                $quantity,
                $days,
                $offset,
                $index + 1
            );
        }

        $completed = collect($bookings)->firstWhere('status', 'completed');
        Review::create([
            'booking_id' => $completed->id,
            'customer_id' => $completed->customer_id,
            'partner_id' => $completed->partner_id,
            'product_id' => $completed->items()->first()->product_id,
            'rating' => 5,
            'review_text' => 'Kamera bersih, baterai sehat, proses pemeriksaan saat ambil sangat jelas, dan pengembalian cepat.',
        ]);
    }

    private function user(string $name, string $email, string $phone, string $role): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => Hash::make('password'),
            'role' => $role,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }

    private function seedPartnerDocuments(PartnerProfile $partner, User $admin, bool $approved): void
    {
        $documents = [
            ['ktp', 'KTP '.$partner->owner_name, '3174'.str_pad((string) $partner->id, 12, '0', STR_PAD_LEFT), true],
            ['nib', 'NIB '.$partner->business_name, 'NIB-2026-'.str_pad((string) $partner->id, 5, '0', STR_PAD_LEFT), true],
            ['rekening', 'Bukti Rekening '.$partner->bank_name, $partner->bank_account_number, true],
            ['foto_usaha', 'Foto Lokasi '.$partner->business_name, null, true],
            ['npwp', 'NPWP '.$partner->business_name, $partner->tax_number, false],
        ];

        foreach ($documents as $index => [$type, $name, $number, $required]) {
            if (! $approved && $type === 'foto_usaha') {
                continue;
            }
            $status = $approved ? 'approved' : ($type === 'rekening' ? 'approved' : 'pending');
            $path = "partner-documents/demo/{$partner->id}-{$type}.pdf";
            Storage::put($path, "%PDF-1.4 Dokumen demo {$name}");
            $partner->documents()->create([
                'document_type' => $type,
                'document_name' => $name,
                'document_number' => $number,
                'issued_at' => now()->subYears(2)->toDateString(),
                'expires_at' => $type === 'ktp' ? null : now()->addYears(3)->toDateString(),
                'is_required' => $required,
                'file_path' => $path,
                'status' => $status,
                'reviewed_by' => $status === 'approved' ? $admin->id : null,
                'reviewed_at' => $status === 'approved' ? now()->subMonths($index + 1) : null,
                'admin_notes' => $status === 'approved' ? 'Dokumen terbaca jelas dan data sesuai profil.' : null,
            ]);
        }
    }

    private function accessoriesFor(string $type): string
    {
        return match ($type) {
            'Lensa' => "Lensa\nFront cap dan rear cap\nLens hood\nFilter UV\nPouch pelindung",
            'Lighting' => "Lampu utama\nLight stand\nReflector\nKabel daya\nTas pelindung",
            'Drone' => "Drone\nRemote controller\n3 baterai\nCharging hub\nPropeller cadangan\nTas",
            'Action Camera' => "Kamera\n2 baterai\nCharger\nMemory card 128 GB\nMounting kit\nHard case",
            'Audio' => "2 transmitter\n1 receiver\nLavalier microphone\nCharging case\nKabel koneksi\nPouch",
            'Stabilizer' => "Gimbal\nTripod grip\nFocus motor\nQuick release plate\nKabel kontrol\nTas",
            default => "Body kamera\nLensa kit\n2 baterai\nCharger\nMemory card 128 GB\nStrap\nTas kamera",
        };
    }

    private function createBooking(
        User $admin,
        User $customer,
        Product $product,
        string $status,
        int $quantity,
        int $days,
        int $offset,
        int $sequence
    ): Booking {
        $past = in_array($status, ['ongoing', 'returned', 'completed', 'cancelled']);
        $start = $past ? now()->subDays($offset)->startOfDay() : now()->addDays($offset)->startOfDay();
        $end = $start->copy()->addDays($days);
        $subtotal = (float) $product->price * $days * $quantity;
        $deposit = (float) $product->security_deposit * $quantity;
        $fee = round($subtotal * .05);
        $extra = $status === 'returned' && $sequence === 7 ? 175000 : 0;
        $total = $subtotal + $deposit + $fee + $extra;
        $paid = in_array($status, ['ready_pickup', 'ongoing', 'returned', 'completed']);
        $paymentStatus = $paid ? 'paid' : 'unpaid';
        if ($status === 'confirmed' && $sequence === 3) {
            $paymentStatus = 'waiting_confirmation';
        }
        if ($status === 'confirmed' && $sequence === 2) {
            $paymentStatus = 'rejected';
        }
        if ($extra > 0) {
            $paymentStatus = 'unpaid';
        }

        $createdAt = now()->subDays(max(1, $offset + 2));
        $booking = Booking::create([
            'booking_code' => 'SW-'.$createdAt->format('Ymd').'-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
            'customer_id' => $customer->id,
            'partner_id' => $product->partner_id,
            'booking_type' => 'rental',
            'pickup_method' => 'store_pickup',
            'pickup_note' => $product->partner->pickup_note,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'customer_email' => $customer->email,
            'customer_address' => $customer->id % 2 ? 'Jl. Melati No. 12, Jakarta Selatan' : 'Jl. Mawar No. 8, Depok',
            'identity_number' => $customer->id % 2 ? '3174000000000001' : '3276000000000002',
            'identity_file' => $customer->id % 2 ? 'customer-identities/demo-ayu.pdf' : 'customer-identities/demo-dimas.pdf',
            'start_at' => $start,
            'end_at' => $end,
            'subtotal_amount' => $subtotal,
            'deposit_amount' => $deposit,
            'platform_fee' => $fee,
            'late_fee' => $extra,
            'damage_fee' => 0,
            'total_amount' => $total,
            'payment_status' => $paymentStatus,
            'status' => $status,
            'customer_notes' => 'Mohon siapkan seluruh kelengkapan dan pastikan baterai terisi.',
            'partner_notes' => in_array($status, ['ready_pickup', 'ongoing', 'returned', 'completed']) ? 'Unit telah diperiksa dan dinyatakan siap.' : null,
            'return_condition' => in_array($status, ['returned', 'completed']) ? 'Kamera kembali lengkap, fungsi normal, dan tidak ditemukan kerusakan baru.' : null,
            'deposit_status' => $status === 'completed' ? 'refunded' : ($paid ? 'held' : ($status === 'cancelled' ? 'not_applicable' : 'pending')),
            'confirmed_at' => in_array($status, ['confirmed', 'ready_pickup', 'ongoing', 'returned', 'completed']) ? $createdAt->copy()->addHours(2) : null,
            'ready_pickup_at' => in_array($status, ['ready_pickup', 'ongoing', 'returned', 'completed']) ? $start->copy()->subDay()->setHour(16) : null,
            'picked_up_at' => in_array($status, ['ongoing', 'returned', 'completed']) ? $start->copy()->setHour(9) : null,
            'returned_at' => in_array($status, ['returned', 'completed']) ? $end->copy()->setHour(16) : null,
            'completed_at' => $status === 'completed' ? $end->copy()->setHour(18) : null,
            'cancelled_at' => $status === 'cancelled' ? $createdAt->copy()->addHours(4) : null,
            'cancelled_by' => $status === 'cancelled' ? $customer->id : null,
            'cancelled_reason' => $status === 'cancelled' ? 'Jadwal produksi berubah.' : null,
            'deposit_refunded_at' => $status === 'completed' ? $end->copy()->addDay()->setHour(10) : null,
            'created_at' => $createdAt,
            'updated_at' => now(),
        ]);
        $booking->items()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price_per_unit' => $product->price,
            'price_unit' => 'day',
            'rental_days' => $days,
            'start_at' => $start,
            'end_at' => $end,
            'subtotal' => $subtotal,
            'item_status' => match ($status) {
                'ready_pickup' => 'prepared',
                'ongoing' => 'ongoing',
                'returned' => 'returned',
                'completed' => 'completed',
                'cancelled' => 'cancelled',
                default => 'booked',
            },
        ]);

        if ($paid || in_array($paymentStatus, ['waiting_confirmation', 'rejected'])) {
            $paymentAmount = $subtotal + $deposit + $fee;
            $paymentCode = 'PAY-'.$createdAt->format('ymd').'-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $proofPath = "payment-proofs/demo-{$sequence}.pdf";
            Storage::put($proofPath, "%PDF-1.4 Bukti transfer demo {$paymentCode}");
            $paymentStatusRow = in_array($paymentStatus, ['waiting_confirmation', 'rejected']) ? $paymentStatus : 'paid';
            $booking->payments()->create([
                'payment_code' => $paymentCode,
                'method' => 'bank_transfer',
                'sender_name' => $customer->name,
                'sender_bank' => $sequence % 2 ? 'BCA' : 'Mandiri',
                'sender_account' => $sequence % 2 ? '1230009876' : '1370098765432',
                'transfer_at' => $createdAt->copy()->addHours(5),
                'amount' => $paymentAmount,
                'proof_file' => $proofPath,
                'status' => $paymentStatusRow,
                'paid_at' => $paymentStatusRow === 'paid' ? $createdAt->copy()->addHours(6) : null,
                'verified_by' => in_array($paymentStatusRow, ['paid', 'rejected']) ? $admin->id : null,
                'verified_at' => in_array($paymentStatusRow, ['paid', 'rejected']) ? $createdAt->copy()->addHours(6) : null,
                'notes' => $paymentStatusRow === 'paid' ? 'Nominal dan rekening pengirim sesuai.' : null,
                'rejection_reason' => $paymentStatusRow === 'rejected' ? 'Nama pengirim pada bukti transfer tidak terbaca jelas.' : null,
            ]);
        }

        return $booking;
    }
}

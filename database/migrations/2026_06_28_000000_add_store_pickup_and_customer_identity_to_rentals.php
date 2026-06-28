<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_profiles', function (Blueprint $table) {
            $table->string('operational_hours', 180)->nullable()->after('phone');
            $table->text('pickup_note')->nullable()->after('operational_hours');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('pickup_method', 40)->default('store_pickup')->after('booking_type');
            $table->text('pickup_note')->nullable()->after('pickup_method');
            $table->string('customer_name', 150)->nullable()->after('customer_id');
            $table->string('customer_phone', 30)->nullable()->after('customer_name');
            $table->string('customer_email', 150)->nullable()->after('customer_phone');
            $table->text('customer_address')->nullable()->after('customer_email');
            $table->string('identity_number', 100)->nullable()->after('customer_address');
            $table->string('identity_file')->nullable()->after('identity_number');
        });

        Schema::table('booking_items', function (Blueprint $table) {
            $table->renameColumn('qty', 'quantity');
            $table->renameColumn('price', 'price_per_unit');
            $table->renameColumn('duration', 'rental_days');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE bookings MODIFY status VARCHAR(40) NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE products MODIFY status VARCHAR(40) NOT NULL DEFAULT 'draft'");

        DB::table('partner_profiles')->whereNull('operational_hours')->update([
            'operational_hours' => 'Senin–Sabtu, 08.00–20.00 WIB',
        ]);
        DB::table('partner_profiles')->whereNull('pickup_note')->update([
            'pickup_note' => 'Tunjukkan kode booking dan identitas asli saat mengambil barang.',
        ]);

        DB::table('bookings')->whereIn('status', ['waiting_payment', 'paid'])->update(['status' => 'confirmed']);
        DB::table('bookings')->where('status', 'prepared')->update(['status' => 'ready_pickup']);
        DB::table('bookings')->where('status', 'rejected')->update(['status' => 'cancelled']);

        DB::table('bookings')->orderBy('id')->eachById(function ($booking) {
            DB::table('bookings')->where('id', $booking->id)->update([
                'booking_code' => Str::uuid()->toString(),
            ]);
        });

        $dailySequences = [];
        DB::table('bookings')->orderBy('created_at')->orderBy('id')->get()->each(function ($booking) use (&$dailySequences) {
            $date = Carbon::parse($booking->created_at)->format('Ymd');
            $dailySequences[$date] = ($dailySequences[$date] ?? 0) + 1;

            DB::table('bookings')->where('id', $booking->id)->update([
                'booking_code' => 'SW-'.$date.'-'.str_pad((string) $dailySequences[$date], 4, '0', STR_PAD_LEFT),
            ]);
        });

        DB::table('bookings')
            ->whereNull('customer_name')
            ->orderBy('id')
            ->eachById(function ($booking) {
                $customer = DB::table('users')->where('id', $booking->customer_id)->first();
                if (! $customer) {
                    return;
                }

                DB::table('bookings')->where('id', $booking->id)->update([
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'customer_email' => $customer->email,
                ]);
            });
    }

    public function down(): void
    {
        DB::table('bookings')->where('status', 'ready_pickup')->update(['status' => 'prepared']);
        DB::table('bookings')->where('status', 'returned')->update(['status' => 'completed']);
        DB::table('products')->where('status', 'archived')->update(['status' => 'inactive']);

        DB::statement("ALTER TABLE bookings MODIFY status ENUM('pending','confirmed','waiting_payment','paid','prepared','ongoing','completed','cancelled','rejected','disputed') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE products MODIFY status ENUM('draft','pending_review','active','inactive','rejected') NOT NULL DEFAULT 'draft'");

        Schema::table('products', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('booking_items', function (Blueprint $table) {
            $table->renameColumn('quantity', 'qty');
            $table->renameColumn('price_per_unit', 'price');
            $table->renameColumn('rental_days', 'duration');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'pickup_method', 'pickup_note', 'customer_name', 'customer_phone',
                'customer_email', 'customer_address', 'identity_number', 'identity_file',
            ]);
        });

        Schema::table('partner_profiles', function (Blueprint $table) {
            $table->dropColumn(['operational_hours', 'pickup_note']);
        });
    }
};

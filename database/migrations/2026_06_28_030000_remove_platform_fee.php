<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            DB::table('bookings')
                ->where('platform_fee', '>', 0)
                ->orderBy('id')
                ->eachById(function (object $booking): void {
                    $fee = (float) $booking->platform_fee;
                    $payment = DB::table('payments')
                        ->where('booking_id', $booking->id)
                        ->orderBy('id')
                        ->first();

                    if ($payment && (float) $payment->amount >= $fee) {
                        DB::table('payments')->where('id', $payment->id)->update([
                            'amount' => max(0, (float) $payment->amount - $fee),
                            'updated_at' => now(),
                        ]);
                    }

                    DB::table('bookings')->where('id', $booking->id)->update([
                        'platform_fee' => 0,
                        'total_amount' => max(0, (float) $booking->total_amount - $fee),
                        'updated_at' => now(),
                    ]);
                });

            DB::table('system_settings')->updateOrInsert(
                ['key' => 'platform_fee_percent'],
                ['value' => '0', 'type' => 'number', 'updated_at' => now(), 'created_at' => now()]
            );
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            DB::table('bookings')
                ->orderBy('id')
                ->eachById(function (object $booking): void {
                    $fee = round((float) $booking->subtotal_amount * 0.05);
                    $payment = DB::table('payments')
                        ->where('booking_id', $booking->id)
                        ->orderBy('id')
                        ->first();

                    if ($payment) {
                        DB::table('payments')->where('id', $payment->id)->update([
                            'amount' => (float) $payment->amount + $fee,
                            'updated_at' => now(),
                        ]);
                    }

                    DB::table('bookings')->where('id', $booking->id)->update([
                        'platform_fee' => $fee,
                        'total_amount' => (float) $booking->total_amount + $fee,
                        'updated_at' => now(),
                    ]);
                });

            DB::table('system_settings')->updateOrInsert(
                ['key' => 'platform_fee_percent'],
                ['value' => '5', 'type' => 'number', 'updated_at' => now(), 'created_at' => now()]
            );
        });
    }
};

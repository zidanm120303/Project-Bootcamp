<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_profiles', function (Blueprint $table) {
            $table->string('business_email', 150)->nullable()->after('phone');
            $table->string('tax_number', 100)->nullable()->after('business_email');
            $table->string('bank_name', 100)->nullable()->after('tax_number');
            $table->string('bank_account_number', 100)->nullable()->after('bank_name');
            $table->string('bank_account_holder', 150)->nullable()->after('bank_account_number');
            $table->text('admin_notes')->nullable()->after('verification_status');
        });

        Schema::table('partner_documents', function (Blueprint $table) {
            $table->string('document_name', 150)->nullable()->after('document_type');
            $table->date('issued_at')->nullable()->after('document_number');
            $table->date('expires_at')->nullable()->after('issued_at');
            $table->boolean('is_required')->default(true)->after('expires_at');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('brand', 100)->nullable()->after('name');
            $table->string('model', 120)->nullable()->after('brand');
            $table->string('camera_type', 80)->nullable()->after('model');
            $table->string('sensor_type', 100)->nullable()->after('camera_type');
            $table->decimal('resolution_mp', 6, 2)->nullable()->after('sensor_type');
            $table->string('video_resolution', 100)->nullable()->after('resolution_mp');
            $table->string('lens_mount', 80)->nullable()->after('video_resolution');
            $table->string('condition_label', 80)->nullable()->after('lens_mount');
            $table->text('included_accessories')->nullable()->after('condition_label');
            $table->text('rental_terms')->nullable()->after('included_accessories');
            $table->decimal('security_deposit', 15, 2)->default(0)->after('price');
            $table->decimal('replacement_value', 15, 2)->nullable()->after('security_deposit');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('confirmed_at')->nullable()->after('status');
            $table->timestamp('ready_pickup_at')->nullable()->after('confirmed_at');
            $table->timestamp('picked_up_at')->nullable()->after('ready_pickup_at');
            $table->timestamp('returned_at')->nullable()->after('picked_up_at');
            $table->timestamp('completed_at')->nullable()->after('returned_at');
            $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            $table->text('return_condition')->nullable()->after('partner_notes');
            $table->decimal('late_fee', 15, 2)->default(0)->after('return_condition');
            $table->decimal('damage_fee', 15, 2)->default(0)->after('late_fee');
            $table->string('deposit_status', 40)->default('pending')->after('damage_fee');
            $table->timestamp('deposit_refunded_at')->nullable()->after('deposit_status');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('sender_name', 150)->nullable()->after('method');
            $table->string('sender_bank', 100)->nullable()->after('sender_name');
            $table->string('sender_account', 100)->nullable()->after('sender_bank');
            $table->timestamp('transfer_at')->nullable()->after('sender_account');
            $table->text('rejection_reason')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['sender_name', 'sender_bank', 'sender_account', 'transfer_at', 'rejection_reason']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'confirmed_at', 'ready_pickup_at', 'picked_up_at', 'returned_at',
                'completed_at', 'cancelled_at', 'return_condition', 'late_fee',
                'damage_fee', 'deposit_status', 'deposit_refunded_at',
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'brand', 'model', 'camera_type', 'sensor_type', 'resolution_mp',
                'video_resolution', 'lens_mount', 'condition_label', 'included_accessories',
                'rental_terms', 'security_deposit', 'replacement_value',
            ]);
        });

        Schema::table('partner_documents', function (Blueprint $table) {
            $table->dropColumn(['document_name', 'issued_at', 'expires_at', 'is_required']);
        });

        Schema::table('partner_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'business_email', 'tax_number', 'bank_name', 'bank_account_number',
                'bank_account_holder', 'admin_notes',
            ]);
        });
    }
};

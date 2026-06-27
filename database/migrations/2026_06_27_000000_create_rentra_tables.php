<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('business_name', 180);
            $table->string('business_type', 100)->nullable();
            $table->string('owner_name', 150);
            $table->string('phone', 30);
            $table->text('address');
            $table->string('city', 100)->index();
            $table->string('province', 100);
            $table->string('postal_code', 20)->nullable();
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('banner_path')->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected', 'suspended'])->default('pending')->index();
            $table->unsignedTinyInteger('trusted_score')->default(0);
            $table->boolean('is_trusted')->default(false)->index();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('partner_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('partner_profiles')->cascadeOnDelete();
            $table->string('document_type', 80);
            $table->string('document_number', 100)->nullable();
            $table->string('file_path');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name', 120);
            $table->string('slug', 150)->unique();
            $table->string('icon', 100)->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('partner_profiles')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name', 180);
            $table->string('slug', 220)->unique();
            $table->enum('product_type', ['rental', 'sale', 'service'])->index();
            $table->longText('description');
            $table->decimal('price', 15, 2);
            $table->enum('price_unit', ['hour', 'day', 'week', 'month', 'service', 'item']);
            $table->unsignedInteger('stock_total')->default(1);
            $table->unsignedInteger('min_rent_duration')->default(1);
            $table->unsignedInteger('max_rent_duration')->nullable();
            $table->string('location_city', 100)->index();
            $table->text('location_address')->nullable();
            $table->enum('status', ['draft', 'pending_review', 'active', 'inactive', 'rejected'])->default('draft')->index();
            $table->text('admin_notes')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->unsignedInteger('total_reviews')->default(0);
            $table->timestamps();
            $table->index(['category_id', 'status']);
            $table->index(['partner_id', 'status']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('unit_code', 80);
            $table->string('serial_number', 120)->nullable();
            $table->enum('condition_status', ['good', 'maintenance', 'damaged', 'lost'])->default('good');
            $table->enum('availability_status', ['available', 'rented', 'blocked'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'unit_code']);
        });

        Schema::create('product_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('capacity')->default(1);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        Schema::create('product_blackout_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('reason', 180)->nullable();
            $table->timestamps();
            $table->index(['product_id', 'start_at', 'end_at']);
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 40)->unique();
            $table->foreignId('customer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('partner_id')->constrained('partner_profiles')->restrictOnDelete();
            $table->enum('booking_type', ['rental', 'sale', 'service', 'mixed']);
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->decimal('subtotal_amount', 15, 2);
            $table->decimal('deposit_amount', 15, 2)->default(0);
            $table->decimal('platform_fee', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->enum('payment_status', ['unpaid', 'waiting_confirmation', 'paid', 'rejected', 'refunded'])->default('unpaid')->index();
            $table->enum('status', ['pending', 'confirmed', 'waiting_payment', 'paid', 'prepared', 'ongoing', 'completed', 'cancelled', 'rejected', 'disputed'])->default('pending')->index();
            $table->text('customer_notes')->nullable();
            $table->text('partner_notes')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancelled_reason')->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'status']);
            $table->index(['partner_id', 'status']);
        });

        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('price', 15, 2);
            $table->string('price_unit', 30);
            $table->unsignedInteger('duration')->default(1);
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->decimal('subtotal', 15, 2);
            $table->enum('item_status', ['booked', 'prepared', 'ongoing', 'returned', 'completed', 'cancelled'])->default('booked');
            $table->timestamps();
            $table->index(['product_id', 'start_at', 'end_at']);
            $table->index(['booking_id', 'product_id']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('payment_code', 40)->unique();
            $table->enum('method', ['bank_transfer', 'qris', 'cash', 'gateway'])->default('bank_transfer');
            $table->decimal('amount', 15, 2);
            $table->string('proof_file')->nullable();
            $table->enum('status', ['pending', 'waiting_confirmation', 'paid', 'rejected', 'refunded'])->default('pending')->index();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['booking_id', 'status']);
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('partner_profiles')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('review_text')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->unique(['booking_id', 'product_id']);
        });

        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('issue_type', 100);
            $table->text('description');
            $table->string('evidence_file')->nullable();
            $table->enum('status', ['open', 'reviewed', 'waiting_partner_response', 'waiting_customer_response', 'resolved', 'rejected'])->default('open')->index();
            $table->text('admin_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 180);
            $table->text('message');
            $table->string('type', 80);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('type', 40)->default('string');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'system_settings', 'notifications', 'disputes', 'reviews', 'payments',
            'booking_items', 'bookings', 'product_blackout_dates', 'product_availabilities',
            'product_units', 'product_images', 'products', 'categories',
            'partner_documents', 'partner_profiles',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};

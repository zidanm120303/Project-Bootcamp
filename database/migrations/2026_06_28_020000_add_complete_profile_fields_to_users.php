<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_path')->nullable()->after('phone');
            $table->date('date_of_birth')->nullable()->after('avatar_path');
            $table->string('gender', 30)->nullable()->after('date_of_birth');
            $table->string('profession', 120)->nullable()->after('gender');
            $table->text('address')->nullable()->after('profession');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('province', 100)->nullable()->after('city');
            $table->string('postal_code', 20)->nullable()->after('province');
            $table->string('identity_type', 40)->nullable()->after('postal_code');
            $table->string('identity_number', 100)->nullable()->after('identity_type');
            $table->string('identity_file')->nullable()->after('identity_number');
            $table->string('emergency_contact_name', 150)->nullable()->after('identity_file');
            $table->string('emergency_contact_phone', 30)->nullable()->after('emergency_contact_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar_path', 'date_of_birth', 'gender', 'profession', 'address',
                'city', 'province', 'postal_code', 'identity_type', 'identity_number',
                'identity_file', 'emergency_contact_name', 'emergency_contact_phone',
            ]);
        });
    }
};

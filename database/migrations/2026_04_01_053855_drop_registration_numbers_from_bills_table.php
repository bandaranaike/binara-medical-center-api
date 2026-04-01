<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropUnique(['bill_registration_number']);
            $table->dropUnique(['booking_registration_number']);
            $table->dropColumn(['bill_registration_number', 'booking_registration_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->string('bill_registration_number')->nullable()->after('uuid');
            $table->string('booking_registration_number')->nullable()->after('bill_registration_number');
            $table->unique('bill_registration_number');
            $table->unique('booking_registration_number');
        });
    }
};

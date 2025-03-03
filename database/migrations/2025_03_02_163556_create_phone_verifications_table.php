<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('phone_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('phone_number')->index(); // Store phone number
            $table->string('otp'); // Store the OTP
            $table->timestamp('expires_at'); // OTP expiration time
            $table->timestamp('verified_at')->nullable(); // Timestamp when phone is verified
            $table->string('token')->unique(); // Unique token for verification
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phone_verifications');
    }
};

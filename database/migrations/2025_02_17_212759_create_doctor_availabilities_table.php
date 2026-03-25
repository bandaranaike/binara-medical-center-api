<?php

use App\Enums\DoctorAvailabilityStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('doctor_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors');
            $table->date('date');
            $table->time('time')->nullable();
            $table->integer('seats')->nullable();
            $table->integer('available_seats')->nullable();
            $table->enum('status', DoctorAvailabilityStatus::toArray())->default(DoctorAvailabilityStatus::ACTIVE);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_availabilities');
    }
};

<?php

use App\Enums\Weekday;
use App\Enums\DoctorRecurring;
use App\Enums\DoctorScheduleStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->enum('weekday', Weekday::toArray())->default(Weekday::SATURDAY);
            $table->time('time')->default('17:00');
            $table->enum('recurring', DoctorRecurring::toArray())->default(DoctorRecurring::WEEKLY);
            $table->integer('seats')->default(20);
            $table->enum('status', DoctorScheduleStatus::toArray())->default(DoctorScheduleStatus::ACTIVE);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_schedules');
    }
};

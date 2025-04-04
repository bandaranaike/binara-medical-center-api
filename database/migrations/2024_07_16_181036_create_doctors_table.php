<?php

use App\Enums\AppointmentType;
use App\Models\Doctor;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('hospital_id')->nullable()->constrained('hospitals');
            $table->foreignId('specialty_id')->nullable()->constrained('specialties');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('telephone')->nullable();
            $table->enum('doctor_type', AppointmentType::toArray())->default(AppointmentType::SPECIALIST);
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};

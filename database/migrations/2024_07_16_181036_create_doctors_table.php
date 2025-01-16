<?php

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
            $table->string('name');
            $table->foreignId('hospital_id')->nullable()->constrained('hospitals');
            $table->foreignId('specialty_id')->nullable()->constrained('specialties');
            $table->string('telephone')->nullable();
            $table->enum('doctor_type', [Doctor::DOCTOR_TYPE_DENTAL, Doctor::DOCTOR_TYPE_OPD, Doctor::DOCTOR_TYPE_SPECIALIST])->default(Doctor::DOCTOR_TYPE_SPECIALIST);
            $table->string('email')->unique()->nullable();
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

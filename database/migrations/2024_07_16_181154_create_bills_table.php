<?php

use App\Enums\AppointmentType;
use App\Enums\BillPaymentStatus;
use App\Enums\BillStatus;
use App\Enums\PaymentType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->decimal('system_amount')->default(0);
            $table->decimal('bill_amount')->default(0);
            $table->foreignId('patient_id')->constrained('patients');
            $table->foreignId('doctor_id')->nullable()->constrained('doctors');
            $table->dateTime('date')->useCurrent();
            $table->enum('status', BillStatus::toArray())->default(BillStatus::DOCTOR);
            $table->enum('payment_type', [PaymentType::toArray()])->default(PaymentType::CASH);
            $table->enum('payment_status', BillPaymentStatus::toArray())->default(BillPaymentStatus::PENDING);
            $table->enum('appointment_type', AppointmentType::toArray())->default(AppointmentType::SPECIALIST);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};

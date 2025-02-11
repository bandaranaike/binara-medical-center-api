<?php

use App\Models\Bill;
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
            $table->string('status')->default('doctor');
            $table->enum('payment_type', [Bill::PAYMENT_TYPE_CASH, Bill::PAYMENT_TYPE_CARD, Bill::PAYMENT_TYPE_ONLINE])
                ->default(Bill::PAYMENT_TYPE_CASH);
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

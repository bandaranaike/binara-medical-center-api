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
        Schema::create('daily_patient_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->unsignedBigInteger('doctor_id');
            $table->date('queue_date');
            $table->integer('queue_number');
            $table->integer('order_number');
            $table->timestamps();

            // Foreign keys
            $table->foreign('bill_id')->references('id')->on('bills')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');

            // Unique constraint for doctor and date (to ensure reset behavior)
            $table->unique(['doctor_id', 'queue_date', 'queue_number']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_patient_queues');
    }
};

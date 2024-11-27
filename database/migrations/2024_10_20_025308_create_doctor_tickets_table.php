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
        Schema::create('doctor_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');  // Reference to the doctor
            $table->integer('ticket_number');  // Ticket number for the day
            $table->date('date');  // Date for the ticketing system
            $table->timestamps();

            // Unique constraint to ensure no ticket clashes for a doctor on a specific day
            $table->unique(['doctor_id', 'ticket_number', 'date']);

            // Add a foreign key relationship if you have a doctors table
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_tickets');
    }
};

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
        Schema::create('patients_medicines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_item_id');
            $table->unsignedBigInteger('medicine_id');
            $table->string('dosage', 120);
            $table->string('type', 40);
            $table->string('duration', 80);
            $table->integer('quantity')->nullable();
            $table->decimal('price')->nullable();
            $table->timestamps();

            $table->foreign('bill_item_id')->references('id')->on('bill_items')->onDelete('no action');
            $table->foreign('medicine_id')->references('id')->on('medicines')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients_medicines');
    }
};

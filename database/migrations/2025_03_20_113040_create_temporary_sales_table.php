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
        Schema::create('temporary_sales', function (Blueprint $table) {
            $table->id();
            $table->integer('stock_id');
            $table->integer('quantity');
            $table->integer('sale_id');
            $table->integer('bill_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_sales');
    }
};

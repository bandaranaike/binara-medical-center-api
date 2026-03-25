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
        Schema::table('patient_medicine_histories', function (Blueprint $table) {
            $table->foreignId('sale_id')->after('medication_frequency_id')->nullable()->constrained('sales');

            $table->dropForeign(['medicine_id']);
            $table->dropColumn('medicine_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_medicine_histories', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->dropColumn('sale_id');

            $table->foreignId('medicine_id')->constrained('medicines');
        });
    }
};

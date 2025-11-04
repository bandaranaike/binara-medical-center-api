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
        Schema::table('medication_frequencies', function (Blueprint $table) {
            $table->integer('frequency')->after('name')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medication_frequencies', function (Blueprint $table) {
            $table->dropColumn('frequency');
        });
    }
};

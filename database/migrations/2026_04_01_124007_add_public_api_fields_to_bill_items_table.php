<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bill_items', function (Blueprint $table) {
            $table->string('service_name')->nullable()->after('service_id');
            $table->string('service_key', 80)->nullable()->after('service_name');
            $table->foreignId('doctor_id')->nullable()->after('service_key')->constrained('doctors');
            $table->decimal('referred_amount', 8, 2)->default(0)->after('bill_amount');
            $table->string('category')->nullable()->after('referred_amount');
            $table->boolean('is_ad_hoc')->default(false)->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('bill_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('doctor_id');
            $table->dropColumn([
                'service_name',
                'service_key',
                'referred_amount',
                'category',
                'is_ad_hoc',
            ]);
        });
    }
};

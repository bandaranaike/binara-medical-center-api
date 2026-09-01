<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table): void {
            $table->decimal('referred_amount')->default(0)->after('system_amount');
        });

        DB::statement('UPDATE bill_items SET referred_amount = ROUND(bill_amount - system_amount, 2)');
        DB::statement('UPDATE bills SET referred_amount = COALESCE((SELECT SUM(referred_amount) FROM bill_items WHERE bill_items.bill_id = bills.id), ROUND(bill_amount - system_amount, 2)), system_amount = COALESCE((SELECT SUM(system_amount) FROM bill_items WHERE bill_items.bill_id = bills.id), system_amount)');

        Schema::table('bills', function (Blueprint $table): void {
            $table->dropColumn('bill_amount');
        });

        Schema::table('bill_items', function (Blueprint $table): void {
            $table->dropColumn('bill_amount');
        });
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table): void {
            $table->decimal('bill_amount')->default(0)->after('referred_amount');
        });

        Schema::table('bill_items', function (Blueprint $table): void {
            $table->decimal('bill_amount')->default(0)->after('system_amount');
        });

        DB::statement('UPDATE bill_items SET bill_amount = referred_amount + system_amount');
        DB::statement('UPDATE bills SET bill_amount = referred_amount + system_amount');

        Schema::table('bills', function (Blueprint $table): void {
            $table->dropColumn('referred_amount');
        });
    }
};

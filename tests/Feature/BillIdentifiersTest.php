<?php

namespace Tests\Feature;

use App\Enums\BillStatus;
use App\Models\Bill;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BillIdentifiersTest extends TestCase
{
    use RefreshDatabase;

    public function test_bills_table_does_not_include_registration_number_columns(): void
    {
        $this->assertFalse(Schema::hasColumn('bills', 'bill_registration_number'));
        $this->assertFalse(Schema::hasColumn('bills', 'booking_registration_number'));
    }

    public function test_bill_creation_still_generates_uuid(): void
    {
        $bill = Bill::query()->create([
            'patient_id' => Patient::factory()->create()->id,
            'status' => BillStatus::DOCTOR,
            'date' => now(),
        ]);

        $this->assertNotNull($bill->fresh()->uuid);
    }
}

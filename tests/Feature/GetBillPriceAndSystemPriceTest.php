<?php

namespace Tests\Feature;

use App\Http\Controllers\Traits\SystemPriceCalculator;
use Tests\TestCase;

class GetBillPriceAndSystemPriceTest extends TestCase
{
    use SystemPriceCalculator;

    public function test_no_service_returns_default_values(): void
    {
        $service = null;
        $billAmount = 100.0;
        $systemAmount = 50.0;

        $result = $this->callMethod(null, 'getBillPriceAndSystemPrice', [$service, $billAmount, $systemAmount]);

        $this->assertEquals([$billAmount, $systemAmount], $result);
    }

    public function test_both_amounts_zero_and_percentage_service(): void
    {
        $service = (object)[
            'is_percentage' => true,
            'system_price' => 20,
            'bill_price' => 100,
        ];

        $result = $this->callMethod(null, 'getBillPriceAndSystemPrice', [$service, 0, 0]);

        $this->assertEquals([100, 20], $result);
    }

    public function test_both_amounts_zero_and_fixed_service(): void
    {
        $service = (object)[
            'is_percentage' => false,
            'system_price' => 50,
            'bill_price' => 200,
        ];

        $result = $this->callMethod(null, 'getBillPriceAndSystemPrice', [$service, 0, 0]);

        $this->assertEquals([200, 50], $result);
    }

    public function test_system_amount_zero_and_percentage_service(): void
    {
        $service = (object)[
            'is_percentage' => true,
            'system_price' => 10,
            'bill_price' => 500,
        ];

        $billAmount = 300;
        $result = $this->callMethod(null, 'getBillPriceAndSystemPrice', [$service, $billAmount, 0]);

        $this->assertEquals([270, 30], $result);
    }

    public function test_system_amount_zero_and_fixed_service(): void
    {
        $service = (object)[
            'is_percentage' => false,
            'system_price' => 25,
            'bill_price' => 100,
        ];

        $billAmount = 50;
        $result = $this->callMethod(null, 'getBillPriceAndSystemPrice', [$service, $billAmount, 0]);

        $this->assertEquals([25, 25], $result);
    }

    /**
     * Helper to call protected/private methods.
     */
    private function callMethod(?object $object, string $method, array $parameters)
    {
        $reflection = new \ReflectionMethod($this, $method);
        $reflection->setAccessible(true);
        return $reflection->invokeArgs($object ?? $this, $parameters);
    }
}

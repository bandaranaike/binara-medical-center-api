<?php

namespace Tests\Feature;

use App\Http\Controllers\BillController;
use Tests\TestCase;
use App\Models\Service;
use App\Models\Bill;
use Mockery;

class PreparePrintDataTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Mocking class constants
        if (!defined('App\\Models\\Bill::FEE_ORIGINAL')) {
            define('App\\Models\\Bill::FEE_ORIGINAL', 'FEE_ORIGINAL');
        }
        if (!defined('App\\Models\\Bill::FEE_INSTITUTION')) {
            define('App\\Models\\Bill::FEE_INSTITUTION', 'FEE_INSTITUTION');
        }
    }

    public function test_valid_service_key_with_non_separate_items()
    {
        $mockService = Mockery::mock(Service::class)->makePartial();
        $mockService->name = 'Specialist channeling';
        $mockService->separate_items = false;

        Service::shouldReceive('where')->with('key', 'channeling-fee')->andReturnSelf();
        Service::shouldReceive('first')->andReturn($mockService);

        $instance = $this->getMockBuilder(BillController::class)
            ->onlyMethods(['calculateSystemPrice'])
            ->getMock();

        $data = $instance->preparePrintData('channeling-fee', 2500.00);

        $this->assertEquals([
            ['name' => 'Specialist channeling FEE_ORIGINAL', 'price' => 2500.00],
        ], $data);
    }

    public function test_valid_service_key_with_separate_items()
    {
        $mockService = Mockery::mock(Service::class);
        $mockService->name = 'Medicines';
        $mockService->separate_items = true;

        Service::shouldReceive('where')->with('key', 'medicine')->andReturnSelf();
        Service::shouldReceive('first')->andReturn($mockService);

        $instance = $this->getMockBuilder(BillController::class)
            ->onlyMethods(['calculateSystemPrice'])
            ->getMock();

        $instance->expects($this->once())
            ->method('calculateSystemPrice')
            ->with('medicine', 100.00, 0)
            ->willReturn(10.00);

        $data = $instance->preparePrintData('medicine', 100.00);

        $this->assertEquals([
            ['name' => 'Medicines FEE_ORIGINAL', 'price' => 100.00],
            ['name' => 'Medicines FEE_INSTITUTION', 'price' => 10.00],
        ], $data);
    }

    public function test_service_key_not_found()
    {
        Service::shouldReceive('where')->with('key', 'non-existent-key')->andReturnSelf();
        Service::shouldReceive('first')->andReturn(null);

        $instance = $this->getMockBuilder(BillController::class)->getMock();

        $data = $instance->preparePrintData('non-existent-key', 500.00);

        $this->assertEmpty($data);
    }

    public function test_valid_service_key_with_zero_bill_amount()
    {
        $mockService = Mockery::mock(Service::class);
        $mockService->name = 'OPD doctor';
        $mockService->separate_items = false;

        Service::shouldReceive('where')->with('key', 'opd-doctor-fee')->andReturnSelf();
        Service::shouldReceive('first')->andReturn($mockService);

        $instance = $this->getMockBuilder(BillController::class)->getMock();

        $data = $instance->preparePrintData('opd-doctor-fee', 0.00);

        $this->assertEquals([
            ['name' => 'OPD doctor FEE_ORIGINAL', 'price' => 0.00],
        ], $data);
    }

    public function test_valid_service_key_with_null_system_amount()
    {
        $mockService = Mockery::mock(Service::class);
        $mockService->name = 'Dental treatments';
        $mockService->separate_items = true;

        Service::shouldReceive('where')->with('key', 'dental-treatments')->andReturnSelf();
        Service::shouldReceive('first')->andReturn($mockService);

        $instance = $this->getMockBuilder(BillController::class)
            ->onlyMethods(['calculateSystemPrice'])
            ->getMock();

        $instance->expects($this->once())
            ->method('calculateSystemPrice')
            ->with('dental-treatments', 50.00, 0)
            ->willReturn(25.00);

        $data = $instance->preparePrintData('dental-treatments', 50.00);

        $this->assertEquals([
            ['name' => 'Dental treatments FEE_ORIGINAL', 'price' => 50.00],
            ['name' => 'Dental treatments FEE_INSTITUTION', 'price' => 25.00],
        ], $data);
    }
}

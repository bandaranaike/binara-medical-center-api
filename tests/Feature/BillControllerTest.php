<?php

namespace Tests\Feature;

use App\Enums\ServiceKey;
use App\Http\Controllers\BillController;
use Tests\TestCase;

class BillControllerTest extends TestCase
{

    public function __construct(string $name, public BillController $billController = new BillController())
    {
        parent::__construct($name);
    }

    /**
     * A basic feature test example.
     */
    public function testCalculateSystemPriceForChanneling(): void
    {
        $systemValue = $this->billController->calculateSystemPrice(ServiceKey::DEFAULT_SPECIALIST_CHANNELING->value, 3500);
        $this->assertEquals(500, $systemValue);
    }

    public function testCalculateSystemPriceForChannelingWithSystemValue(): void
    {
        $systemValue = $this->billController->calculateSystemPrice(ServiceKey::DEFAULT_SPECIALIST_CHANNELING->value, 3500, 600);
        $this->assertEquals(600, $systemValue);
    }


    public function testCalculateSystemPriceForWoundDressing(): void
    {
        $systemValue = $this->billController->calculateSystemPrice(ServiceKey::WOUND_DRESSING->value, 400);
        $this->assertEquals(400, $systemValue);
    }


    public function testCalculateSystemPriceForDentalLab(): void
    {
        $systemValue = $this->billController->calculateSystemPrice(ServiceKey::DENTAL_LAB->value, 5000);
        $this->assertEquals(0, $systemValue);
    }

    public function testCalculateSystemPriceForDentalTreatments(): void
    {
        $systemValue = $this->billController->calculateSystemPrice(ServiceKey::DENTAL_TREATMENTS->value, 8000);
        $this->assertEquals(4000, $systemValue);
    }

    public function testPrintedDataSeperatedWithSystemValue(): void
    {
        $printingValues = $this->billController->preparePrintData(ServiceKey::DEFAULT_SPECIALIST_CHANNELING->value, 3000, 600);
        $this->assertArrayHasKey('name', $printingValues[0]);
        $this->assertArrayHasKey('price', $printingValues[0]);
        $this->assertCount(2, $printingValues);
    }

    public function testPrintedDataSeperatedWithoutSystemValue(): void
    {
        $printingValues = $this->billController->preparePrintData(ServiceKey::DEFAULT_SPECIALIST_CHANNELING->value, 3000);
        $this->assertArrayHasKey('name', $printingValues[0]);
        $this->assertArrayHasKey('price', $printingValues[0]);
        $this->assertCount(2, $printingValues);
    }

    public function testPrintedDataWithoutSystemValue(): void
    {
        $printingValues = $this->billController->preparePrintData(ServiceKey::MEDICINE->value, 400);
        $this->assertArrayHasKey('name', $printingValues[0]);
        $this->assertArrayHasKey('price', $printingValues[0]);
        $this->assertCount(1, $printingValues);
    }
}

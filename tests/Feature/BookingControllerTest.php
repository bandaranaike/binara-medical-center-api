<?php

use App\Enums\BillStatus;
use App\Http\Controllers\BookingController;
use App\Http\Requests\Website\StoreBookingRequest;
use App\Models\Bill;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Mockery;

class BookingControllerTest extends TestCase
{
    private BookingController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new BookingController();
    }

    public function testMakeAppointment_ValidRequest_ReturnsSuccessResponse()
    {
        // Mock the StoreBookingRequest
        $requestData = [
            'service_type' => 'channeling',
            'bill_id' => 1,
            'bill_amount' => 1000,
            'system_amount' => 900,
            'doctor_id' => 1,
            'date' => '2024-03-15', // Example date
            'time' => '10:00',      // Example time
            // ... other required fields from StoreBookingRequest
        ];

        $request = new StoreBookingRequest();
        $request->merge($requestData);  // Simulate request data

        // Mock the Service
        $service = Mockery::mock(Service::class);
        $service->id = 1;
        $this->controller->shouldReceive('getService')->with('channeling')->andReturn($service);

        // Mock Bill Creation (or use a factory for testing)
        $bill = new Bill(['id' => 1, 'status' => BillStatus::BOOKED]); // Use factory in real tests
        Bill::shouldReceive('firstOrCreate')->with(['id' => 1], [...$requestData, 'status' => BillStatus::BOOKED])->andReturn($bill);

        // Mock Bill Item Insertion
        $this->controller->shouldReceive('insertBillItems')->with($service->id, $requestData['bill_amount'], $requestData['system_amount'], $bill->id);

        // Mock Daily Patient Queue Creation
        $bookingNumber = 'B000123'; // Example Booking Number
        $this->controller->shouldReceive('createDailyPatientQueue')->with($bill->id, $requestData['doctor_id'])->andReturn($bookingNumber);

        // Expected Response Data (replace with actual expected values)
        $expectedResponse = [
            "doctor_name" => null, // You might need to mock doctor retrieval to set a name.
            "booking_number" => $bookingNumber,
            "date" => $requestData['date'],
            "time" => $requestData['time'],
            "reference" => null, // You might need to generate this value
            "generated_at" => now()->toDateTimeString(), // Or appropriate value
            "bill_id" => $bill->id,
        ];


        // Execute the controller method
        $response = $this->controller->makeAppointment($request);

        // Assert the response status code
        $response->assertStatus(200);

        // Assert the response data structure and values (more important than exact time)
        $responseData = $response->json();

        $this->assertArrayHasKey("doctor_name", $responseData);
        $this->assertArrayHasKey("booking_number", $responseData);
        $this->assertArrayHasKey("date", $responseData);
        $this->assertArrayHasKey("time", $responseData);
        $this->assertArrayHasKey("reference", $responseData);
        $this->assertArrayHasKey("generated_at", $responseData);
        $this->assertArrayHasKey("bill_id", $responseData);

        $this->assertEquals($expectedResponse['booking_number'], $responseData['booking_number']);
        $this->assertEquals($expectedResponse['bill_id'], $responseData['bill_id']);
        $this->assertEquals($expectedResponse['date'], $responseData['date']);
        $this->assertEquals($expectedResponse['time'], $responseData['time']);

        Mockery::close(); // Important to close Mockery after tests
    }

    // Add more test cases for invalid requests, exceptions, etc.
    // For example:
    // public function testMakeAppointment_InvalidRequest_ReturnsValidationError() { ... }
    // public function testMakeAppointment_DatabaseError_ReturnsInternalServerError() { ... }
}

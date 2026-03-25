<?php

namespace Tests\Feature\Public;

use App\Enums\AppointmentType;
use App\Enums\DoctorAvailabilityStatus;
use App\Enums\PaymentType;
use App\Events\NewBillCreated;
use App\Models\Bill;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\PublicAppToken;
use App\Models\Role;
use App\Models\Service;
use App\Models\Specialty;
use App\Models\TrustedSite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_routes_require_a_valid_bearer_token(): void
    {
        $trustedSite = TrustedSite::create([
            'domain' => 'desktop.local',
            'api_key' => 'trusted-api-key',
        ]);

        [$status, $payload] = $this->dispatchJsonRequest(
            'GET',
            '/api/public/patients/search?query=0771234567',
            [],
            $this->trustedHeaders($trustedSite),
        );

        $this->assertSame(401, $status);
        $this->assertSame('Unauthorized: Missing public application token.', $payload['message']);
    }

    public function test_public_patient_search_orders_exact_telephone_matches_first(): void
    {
        [$trustedSite, $token] = $this->createTrustedSiteWithToken();

        $exactMatch = Patient::factory()->create([
            'name' => 'John Exact',
            'telephone' => '+94771234567',
        ]);

        Patient::factory()->create([
            'name' => 'John Partial',
            'telephone' => '+94770000000',
        ]);

        Patient::factory()->create([
            'name' => 'Another John',
            'telephone' => '+94771230000',
        ]);

        [$status, $payload] = $this->dispatchJsonRequest(
            'GET',
            '/api/public/patients/search?query=0771234567',
            [],
            $this->trustedHeaders($trustedSite, $token),
        );

        $this->assertSame(200, $status);
        $this->assertSame($exactMatch->id, $payload['data'][0]['id']);
    }

    public function test_public_patient_create_returns_conflict_for_duplicate_telephone(): void
    {
        [$trustedSite, $token] = $this->createTrustedSiteWithToken();

        Patient::factory()->create([
            'telephone' => '+94771234567',
        ]);

        [$status, $payload] = $this->dispatchJsonRequest(
            'POST',
            '/api/public/patients',
            [
                'name' => 'John Doe',
                'telephone' => '0771234567',
                'email' => 'john@example.com',
                'age' => 30,
                'gender' => 'male',
                'address' => 'Colombo',
            ],
            $this->trustedHeaders($trustedSite, $token),
        );

        $this->assertSame(409, $status);
        $this->assertSame('Patient already exists for the given telephone number.', $payload['message']);
    }

    public function test_public_patient_upsert_updates_existing_patient_by_telephone(): void
    {
        [$trustedSite, $token] = $this->createTrustedSiteWithToken();

        $patient = Patient::factory()->create([
            'telephone' => '+94771234567',
            'address' => 'Old Address',
        ]);

        [$status, $payload] = $this->dispatchJsonRequest(
            'POST',
            '/api/public/patients/upsert',
            [
                'name' => $patient->name,
                'telephone' => '0771234567',
                'email' => $patient->email,
                'age' => $patient->age,
                'gender' => $patient->gender,
                'address' => 'New Address',
            ],
            $this->trustedHeaders($trustedSite, $token),
        );

        $this->assertSame(200, $status);
        $this->assertSame('updated', $payload['action']);
        $this->assertSame($patient->id, $payload['patient']['id']);
        $this->assertSame('New Address', $payload['patient']['address']);
    }

    public function test_public_doctor_list_supports_filtering_and_sorting(): void
    {
        [$trustedSite, $token] = $this->createTrustedSiteWithToken();

        $specialty = Specialty::query()->create(['name' => 'Cardiology']);
        $hospital = Hospital::query()->create(['name' => 'General Hospital', 'location' => 'Colombo']);
        $doctorRole = Role::query()->create([
            'name' => 'Doctor',
            'key' => 'doctor',
            'description' => 'Doctor role',
        ]);

        $zUser = User::factory()->create(['role_id' => $doctorRole->id]);
        $aUser = User::factory()->create(['role_id' => $doctorRole->id]);

        $zebra = Doctor::query()->create([
            'name' => 'Dr. Zebra',
            'hospital_id' => $hospital->id,
            'specialty_id' => $specialty->id,
            'user_id' => $zUser->id,
            'telephone' => '+94770000002',
            'email' => 'zebra@example.com',
            'doctor_type' => AppointmentType::OPD->value,
        ]);

        $alpha = Doctor::query()->create([
            'name' => 'Dr. Alpha',
            'hospital_id' => $hospital->id,
            'specialty_id' => $specialty->id,
            'user_id' => $aUser->id,
            'telephone' => '+94770000001',
            'email' => 'alpha@example.com',
            'doctor_type' => AppointmentType::OPD->value,
        ]);

        DoctorAvailability::query()->create([
            'doctor_id' => $zebra->id,
            'date' => now()->toDateString(),
            'time' => '10:00:00',
            'seats' => 10,
            'available_seats' => 5,
            'status' => DoctorAvailabilityStatus::ACTIVE->value,
        ]);

        DoctorAvailability::query()->create([
            'doctor_id' => $alpha->id,
            'date' => now()->toDateString(),
            'time' => '09:00:00',
            'seats' => 10,
            'available_seats' => 3,
            'status' => DoctorAvailabilityStatus::ACTIVE->value,
        ]);

        [$status, $payload] = $this->dispatchJsonRequest(
            'GET',
            '/api/public/doctors?doctor_type=opd&sort[]=name',
            [],
            $this->trustedHeaders($trustedSite, $token),
        );

        $this->assertSame(200, $status);
        $this->assertSame('Dr. Alpha', $payload['data'][0]['name']);
        $this->assertSame('Dr. Zebra', $payload['data'][1]['name']);
    }

    public function test_public_doctor_list_only_returns_doctors_available_for_the_requested_date(): void
    {
        [$trustedSite, $token] = $this->createTrustedSiteWithToken();

        $specialty = Specialty::query()->create(['name' => 'Neurology']);
        $hospital = Hospital::query()->create(['name' => 'City Hospital', 'location' => 'Colombo']);
        $doctorRole = Role::query()->create([
            'name' => 'Doctor',
            'key' => 'doctor',
            'description' => 'Doctor role',
        ]);

        $todayDoctor = Doctor::query()->create([
            'name' => 'Dr. Today',
            'hospital_id' => $hospital->id,
            'specialty_id' => $specialty->id,
            'user_id' => User::factory()->create(['role_id' => $doctorRole->id])->id,
            'telephone' => '+94770000003',
            'email' => 'today@example.com',
            'doctor_type' => AppointmentType::SPECIALIST->value,
        ]);

        $futureDoctor = Doctor::query()->create([
            'name' => 'Dr. Future',
            'hospital_id' => $hospital->id,
            'specialty_id' => $specialty->id,
            'user_id' => User::factory()->create(['role_id' => $doctorRole->id])->id,
            'telephone' => '+94770000004',
            'email' => 'future@example.com',
            'doctor_type' => AppointmentType::SPECIALIST->value,
        ]);

        $unavailableDoctor = Doctor::query()->create([
            'name' => 'Dr. Full',
            'hospital_id' => $hospital->id,
            'specialty_id' => $specialty->id,
            'user_id' => User::factory()->create(['role_id' => $doctorRole->id])->id,
            'telephone' => '+94770000005',
            'email' => 'full@example.com',
            'doctor_type' => AppointmentType::SPECIALIST->value,
        ]);

        DoctorAvailability::query()->create([
            'doctor_id' => $todayDoctor->id,
            'date' => now()->toDateString(),
            'time' => '09:00:00',
            'seats' => 10,
            'available_seats' => 2,
            'status' => DoctorAvailabilityStatus::ACTIVE->value,
        ]);

        DoctorAvailability::query()->create([
            'doctor_id' => $futureDoctor->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '09:00:00',
            'seats' => 10,
            'available_seats' => 2,
            'status' => DoctorAvailabilityStatus::ACTIVE->value,
        ]);

        DoctorAvailability::query()->create([
            'doctor_id' => $unavailableDoctor->id,
            'date' => now()->toDateString(),
            'time' => '09:00:00',
            'seats' => 10,
            'available_seats' => 0,
            'status' => DoctorAvailabilityStatus::ACTIVE->value,
        ]);

        [$todayStatus, $todayPayload] = $this->dispatchJsonRequest(
            'GET',
            '/api/public/doctors?doctor_type=specialist',
            [],
            $this->trustedHeaders($trustedSite, $token),
        );

        $this->assertSame(200, $todayStatus);
        $this->assertCount(1, $todayPayload['data']);
        $this->assertSame('Dr. Today', $todayPayload['data'][0]['name']);

        [$futureStatus, $futurePayload] = $this->dispatchJsonRequest(
            'GET',
            '/api/public/doctors?doctor_type=specialist&date='.now()->addDay()->toDateString(),
            [],
            $this->trustedHeaders($trustedSite, $token),
        );

        $this->assertSame(200, $futureStatus);
        $this->assertCount(1, $futurePayload['data']);
        $this->assertSame('Dr. Future', $futurePayload['data'][0]['name']);
    }

    public function test_public_bill_create_creates_bill_bill_item_and_queue(): void
    {
        Event::fake([NewBillCreated::class]);

        [$trustedSite, $token] = $this->createTrustedSiteWithToken();
        $patient = Patient::factory()->create();
        $specialty = Specialty::query()->create(['name' => 'General Medicine']);
        $hospital = Hospital::query()->create(['name' => 'Main Hospital', 'location' => 'Kandy']);
        $doctorRole = Role::query()->create([
            'name' => 'Doctor',
            'key' => 'doctor',
            'description' => 'Doctor role',
        ]);
        $doctorUser = User::factory()->create(['role_id' => $doctorRole->id]);

        $doctor = Doctor::query()->create([
            'name' => 'Dr. Public',
            'hospital_id' => $hospital->id,
            'specialty_id' => $specialty->id,
            'user_id' => $doctorUser->id,
            'telephone' => '+94775555555',
            'email' => 'doctor.public@example.com',
            'doctor_type' => AppointmentType::OPD->value,
        ]);

        Service::query()->create([
            'name' => 'OPD doctor',
            'key' => 'opd-doctor',
            'bill_price' => 350,
            'system_price' => 100,
        ]);

        [$status, $payload] = $this->dispatchJsonRequest(
            'POST',
            '/api/public/bills',
            [
                'bill_amount' => 2500,
                'payment_type' => PaymentType::CASH->value,
                'system_amount' => 0,
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'is_booking' => false,
                'service_type' => AppointmentType::OPD->value,
                'shift' => 'morning',
                'date' => '2026-03-25',
            ],
            $this->trustedHeaders($trustedSite, $token),
        );

        $this->assertSame(201, $status);
        $this->assertSame($patient->id, $payload['patient_id']);
        $this->assertSame($doctor->id, $payload['doctor_id']);
        $this->assertSame(PaymentType::CASH->value, $payload['payment_type']);
        $this->assertSame('doctor', $payload['status']);
        $this->assertSame(AppointmentType::OPD->value, $payload['service_type']);

        $bill = Bill::query()->first();

        $this->assertNotNull($bill);
        $this->assertDatabaseHas('bill_items', [
            'bill_id' => $bill->id,
        ]);
        $this->assertDatabaseHas('daily_patient_queues', [
            'bill_id' => $bill->id,
            'doctor_id' => $doctor->id,
            'queue_date' => '2026-03-25',
        ]);

        Event::assertDispatched(NewBillCreated::class);
    }

    private function createTrustedSiteWithToken(): array
    {
        $trustedSite = TrustedSite::create([
            'domain' => 'desktop.local',
            'api_key' => 'trusted-api-key',
        ]);

        [, $plainTextToken] = PublicAppToken::issueForTrustedSite($trustedSite, 'Electron Desktop');

        return [$trustedSite, $plainTextToken];
    }

    private function trustedHeaders(TrustedSite $trustedSite, ?string $token = null): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-API-KEY' => $trustedSite->api_key,
            'Referer' => 'https://'.$trustedSite->domain,
        ];

        if ($token !== null) {
            $headers['Authorization'] = 'Bearer '.$token;
        }

        return $headers;
    }

    private function dispatchJsonRequest(string $method, string $uri, array $payload, array $headers): array
    {
        $server = [];

        foreach ($headers as $header => $value) {
            $normalizedHeader = strtoupper(str_replace('-', '_', $header));

            if (in_array($normalizedHeader, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)) {
                $server[$normalizedHeader] = $value;

                continue;
            }

            $server['HTTP_'.$normalizedHeader] = $value;
        }

        $request = Request::create(
            $uri,
            $method,
            [],
            [],
            [],
            $server,
            empty($payload) ? null : json_encode($payload, JSON_THROW_ON_ERROR),
        );

        $response = app()->handle($request);
        $content = $response->getContent();

        return [
            $response->getStatusCode(),
            $content !== false && $content !== '' ? json_decode($content, true, 512, JSON_THROW_ON_ERROR) : [],
        ];
    }
}

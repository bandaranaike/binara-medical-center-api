<?php

namespace Tests\Feature\Public;

use App\Enums\AppointmentType;
use App\Enums\BillStatus;
use App\Enums\DoctorAvailabilityStatus;
use App\Enums\PaymentType;
use App\Events\NewBillCreated;
use App\Models\Bill;
use App\Models\DailyPatientQueue;
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
        $this->assertSame(Bill::formatBillRegistrationNumber($bill->id), $payload['bill_registration_number']);
        $this->assertNull($payload['booking_registration_number']);
        $this->assertSame(Bill::formatBillRegistrationNumber($bill->id), $bill->bill_registration_number);
        $this->assertNull($bill->booking_registration_number);
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

    public function test_public_booking_make_appointment_skips_phone_verification_and_persists_patient_details(): void
    {
        [$trustedSite, $token] = $this->createTrustedSiteWithToken();

        Role::query()->create([
            'name' => 'Patient',
            'key' => 'patient',
            'description' => 'Patient role',
        ]);

        $doctorRole = Role::query()->create([
            'name' => 'Doctor',
            'key' => 'doctor',
            'description' => 'Doctor role',
        ]);

        $specialty = Specialty::query()->create(['name' => 'Cardiology']);
        $hospital = Hospital::query()->create(['name' => 'Heart Center', 'location' => 'Colombo']);
        $doctor = Doctor::query()->create([
            'name' => 'Dr. Public Booking',
            'hospital_id' => $hospital->id,
            'specialty_id' => $specialty->id,
            'user_id' => User::factory()->create(['role_id' => $doctorRole->id])->id,
            'telephone' => '+94770000011',
            'email' => 'public-booking@example.com',
            'doctor_type' => AppointmentType::SPECIALIST->value,
        ]);

        DoctorAvailability::query()->create([
            'doctor_id' => $doctor->id,
            'date' => '2026-03-27',
            'time' => '09:00:00',
            'seats' => 5,
            'available_seats' => 5,
            'status' => DoctorAvailabilityStatus::ACTIVE->value,
        ]);

        Service::query()->create([
            'name' => 'Specialist Channeling',
            'key' => 'channeling',
            'bill_price' => 2500,
            'system_price' => 500,
        ]);

        [$status, $payload] = $this->dispatchJsonRequest(
            'POST',
            '/api/public/bookings/make-appointment',
            [
                'name' => 'John Public',
                'phone' => '0771234567',
                'email' => 'john.public@example.com',
                'registration_no' => 'REG-PUBLIC-001',
                'address' => 'Colombo 07',
                'age' => 30,
                'doctor_id' => $doctor->id,
                'doctor_type' => AppointmentType::SPECIALIST->value,
                'date' => '2026-03-27',
            ],
            $this->trustedHeaders($trustedSite, $token),
        );

        $this->assertSame(200, $status);
        $this->assertSame('Dr. Public Booking', $payload['doctor_name']);
        $this->assertSame('Cardiology', $payload['doctor_specialty']);
        $this->assertSame(1, $payload['booking_number']);
        $this->assertSame('2026-03-27', $payload['date']);
        $this->assertStringStartsWith('BOOK-', $payload['reference']);
        $this->assertArrayHasKey('generated_at', $payload);
        $this->assertArrayHasKey('bill_id', $payload);

        $bill = Bill::query()->find($payload['bill_id']);

        $this->assertNotNull($bill);
        $this->assertSame('booked', $bill->status);
        $this->assertSame(Bill::formatBillRegistrationNumber($bill->id), $payload['bill_registration_number']);
        $this->assertSame(Bill::formatBookingRegistrationNumber($bill->id), $payload['booking_registration_number']);
        $this->assertSame(Bill::formatBookingRegistrationNumber($bill->id), $payload['reference']);
        $this->assertSame(Bill::formatBillRegistrationNumber($bill->id), $bill->bill_registration_number);
        $this->assertSame(Bill::formatBookingRegistrationNumber($bill->id), $bill->booking_registration_number);
        $this->assertDatabaseHas('patients', [
            'name' => 'John Public',
            'telephone' => '0771234567',
            'registration_no' => 'REG-PUBLIC-001',
            'address' => 'Colombo 07',
        ]);
        $this->assertDatabaseHas('bill_items', [
            'bill_id' => $bill->id,
        ]);
        $this->assertDatabaseHas('daily_patient_queues', [
            'bill_id' => $bill->id,
            'doctor_id' => $doctor->id,
            'queue_date' => '2026-03-27',
            'queue_number' => 1,
        ]);
        $this->assertSame(
            4,
            DoctorAvailability::query()
                ->where('doctor_id', $doctor->id)
                ->where('date', '2026-03-27')
                ->firstOrFail()
                ->available_seats,
        );
    }

    public function test_public_doctors_by_date_supports_type_alias_and_contract_shape(): void
    {
        [$trustedSite, $token] = $this->createTrustedSiteWithToken();

        $specialty = Specialty::query()->create(['name' => 'Pediatrics']);
        $hospital = Hospital::query()->create(['name' => 'Children Hospital', 'location' => 'Colombo']);
        $doctorRole = Role::query()->create([
            'name' => 'Doctor',
            'key' => 'doctor',
            'description' => 'Doctor role',
        ]);

        $doctor = Doctor::query()->create([
            'name' => 'Dr. Alias',
            'hospital_id' => $hospital->id,
            'specialty_id' => $specialty->id,
            'user_id' => User::factory()->create(['role_id' => $doctorRole->id])->id,
            'telephone' => '+94770000021',
            'email' => 'alias@example.com',
            'doctor_type' => AppointmentType::OPD->value,
        ]);

        DoctorAvailability::query()->create([
            'doctor_id' => $doctor->id,
            'date' => '2026-03-30',
            'time' => '09:30:00',
            'seats' => 12,
            'available_seats' => 7,
            'status' => DoctorAvailabilityStatus::ACTIVE->value,
        ]);

        [$status, $payload] = $this->dispatchJsonRequest(
            'GET',
            '/api/public/doctors/by-date?date=2026-03-30&type=opd',
            [],
            $this->trustedHeaders($trustedSite, $token),
        );

        $this->assertSame(200, $status);
        $this->assertSame('Dr. Alias', $payload['data'][0]['name']);
        $this->assertSame('Pediatrics', $payload['data'][0]['specialty']);
        $this->assertSame('2026-03-30', $payload['data'][0]['availability_date']);
        $this->assertSame(7, $payload['data'][0]['available_seats']);
        $this->assertNull($payload['data'][0]['address']);
        $this->assertNull($payload['data'][0]['dental_split_mode']);
        $this->assertNull($payload['data'][0]['dental_split_value']);
    }

    public function test_public_booking_list_and_show_return_date_filtered_bookings(): void
    {
        [$trustedSite, $token] = $this->createTrustedSiteWithToken();
        $booking = $this->createBookedBillWithRelations(date: '2026-03-30', registrationNo: 'REG-001');
        $this->createBookedBillWithRelations(date: '2026-03-31', registrationNo: 'REG-002');

        [$listStatus, $listPayload] = $this->dispatchJsonRequest(
            'GET',
            '/api/public/bookings?date=2026-03-30',
            [],
            $this->trustedHeaders($trustedSite, $token),
        );

        $this->assertSame(200, $listStatus);
        $this->assertCount(1, $listPayload['data']);
        $this->assertSame($booking->id, $listPayload['data'][0]['id']);
        $this->assertSame($booking->booking_registration_number, $listPayload['data'][0]['reference']);
        $this->assertSame('REG-001', $listPayload['data'][0]['patient']['registration_no']);
        $this->assertSame(1, $listPayload['meta']['page']);
        $this->assertSame(1, $listPayload['meta']['total']);

        [$showStatus, $showPayload] = $this->dispatchJsonRequest(
            'GET',
            '/api/public/bookings/'.$booking->id,
            [],
            $this->trustedHeaders($trustedSite, $token),
        );

        $this->assertSame(200, $showStatus);
        $this->assertSame($booking->id, $showPayload['id']);
        $this->assertSame('REG-001', $showPayload['patient']['registration_no']);
        $this->assertSame($booking->doctor->name, $showPayload['doctor_name']);
        $this->assertSame($booking->doctor->specialty->name, $showPayload['doctor_specialty']);
    }

    public function test_public_booking_update_restores_previous_slot_and_regenerates_queue(): void
    {
        [$trustedSite, $token] = $this->createTrustedSiteWithToken();
        $booking = $this->createBookedBillWithRelations(date: '2026-03-30', registrationNo: 'REG-010');
        $newDate = '2026-03-31';

        DoctorAvailability::query()->create([
            'doctor_id' => $booking->doctor_id,
            'date' => $newDate,
            'time' => '09:00:00',
            'seats' => 5,
            'available_seats' => 5,
            'status' => DoctorAvailabilityStatus::ACTIVE->value,
        ]);

        [$status, $payload] = $this->dispatchJsonRequest(
            'PUT',
            '/api/public/bookings/'.$booking->id,
            [
                'patient' => [
                    'name' => 'Updated Patient',
                    'telephone' => '0771234567',
                    'email' => 'updated@example.com',
                    'registration_no' => 'REG-999',
                    'age' => 31,
                    'gender' => 'female',
                    'address' => 'Galle',
                    'birthday' => '1995-02-14',
                ],
                'doctor_id' => $booking->doctor_id,
                'doctor_type' => AppointmentType::SPECIALIST->value,
                'date' => $newDate,
                'shift' => 'morning',
                'payment_type' => PaymentType::CARD->value,
                'service_type' => AppointmentType::SPECIALIST->value,
                'bill_amount' => 3200,
                'system_amount' => 600,
                'items' => [
                    ['name' => 'Consultation', 'price' => 3200],
                ],
            ],
            $this->trustedHeaders($trustedSite, $token),
        );

        $this->assertSame(200, $status);
        $this->assertSame('Booking updated successfully.', $payload['message']);
        $this->assertSame($booking->booking_registration_number, $payload['booking']['reference']);
        $this->assertSame($newDate, $payload['booking']['date']);

        $booking->refresh();

        $this->assertSame($newDate, substr((string) $booking->date, 0, 10));
        $this->assertSame(PaymentType::CARD->value, $booking->payment_type);
        $this->assertSame(3200.0, (float) $booking->bill_amount);
        $this->assertSame(600.0, (float) $booking->system_amount);
        $this->assertSame('Updated Patient', $booking->patient->name);
        $this->assertSame('+94771234567', $booking->patient->telephone);
        $this->assertSame('REG-999', $booking->patient->registration_no);
        $this->assertDatabaseHas('daily_patient_queues', [
            'bill_id' => $booking->id,
            'queue_date' => $newDate,
            'queue_number' => 1,
        ]);
        $this->assertDatabaseMissing('daily_patient_queues', [
            'bill_id' => $booking->id,
            'queue_date' => '2026-03-30',
        ]);
        $this->assertSame(5, DoctorAvailability::query()->where('doctor_id', $booking->doctor_id)->where('date', '2026-03-30')->firstOrFail()->available_seats);
        $this->assertSame(4, DoctorAvailability::query()->where('doctor_id', $booking->doctor_id)->where('date', $newDate)->firstOrFail()->available_seats);
    }

    public function test_public_booking_delete_restores_seat_and_removes_booking_records(): void
    {
        [$trustedSite, $token] = $this->createTrustedSiteWithToken();
        $booking = $this->createBookedBillWithRelations(date: '2026-03-30', registrationNo: 'REG-020');

        [$status, $payload] = $this->dispatchJsonRequest(
            'DELETE',
            '/api/public/bookings/'.$booking->id,
            [],
            $this->trustedHeaders($trustedSite, $token),
        );

        $this->assertSame(200, $status);
        $this->assertSame('Booking deleted successfully.', $payload['message']);
        $this->assertSame($booking->id, $payload['deleted_id']);
        $this->assertSoftDeleted('bills', ['id' => $booking->id]);
        $this->assertDatabaseMissing('daily_patient_queues', ['bill_id' => $booking->id]);
        $this->assertDatabaseMissing('bill_items', ['bill_id' => $booking->id]);
        $this->assertSame(5, DoctorAvailability::query()->where('doctor_id', $booking->doctor_id)->where('date', '2026-03-30')->firstOrFail()->available_seats);
    }

    public function test_public_booking_can_proceed_to_payment(): void
    {
        [$trustedSite, $token] = $this->createTrustedSiteWithToken();
        $booking = $this->createBookedBillWithRelations(date: '2026-03-30', registrationNo: 'REG-030');

        [$status, $payload] = $this->dispatchJsonRequest(
            'POST',
            '/api/public/bookings/'.$booking->id.'/proceed-to-payment',
            [
                'payment_type' => PaymentType::CASH->value,
                'shift' => 'evening',
                'bill_amount' => 4000,
                'system_amount' => 750,
                'items' => [
                    ['name' => 'Consultation', 'price' => 4000],
                ],
            ],
            $this->trustedHeaders($trustedSite, $token),
        );

        $this->assertSame(200, $status);
        $this->assertSame('Booking moved to payment successfully.', $payload['message']);
        $this->assertSame($booking->booking_registration_number, $payload['bill']['reference']);
        $this->assertSame(BillStatus::DOCTOR->value, $payload['bill']['status']);

        $booking->refresh();

        $this->assertSame(BillStatus::DOCTOR->value, $booking->status);
        $this->assertSame(PaymentType::CASH->value, $booking->payment_type);
        $this->assertSame('evening', $booking->shift);
        $this->assertSame(4000.0, (float) $booking->bill_amount);
        $this->assertSame(750.0, (float) $booking->system_amount);
    }

    private function createBookedBillWithRelations(string $date, string $registrationNo): Bill
    {
        $specialty = Specialty::query()->firstOrCreate(['name' => 'Cardiology']);
        $hospital = Hospital::query()->firstOrCreate(['name' => 'Main Hospital'], ['location' => 'Colombo']);
        $doctorRole = Role::query()->firstOrCreate(
            ['key' => 'doctor'],
            ['name' => 'Doctor', 'description' => 'Doctor role'],
        );

        $doctor = Doctor::query()->create([
            'name' => 'Dr. Booking '.$registrationNo,
            'hospital_id' => $hospital->id,
            'specialty_id' => $specialty->id,
            'user_id' => User::factory()->create(['role_id' => $doctorRole->id])->id,
            'telephone' => '+94770000111',
            'email' => strtolower($registrationNo).'@example.com',
            'doctor_type' => AppointmentType::SPECIALIST->value,
        ]);

        $patient = Patient::factory()->create([
            'name' => 'Patient '.$registrationNo,
            'telephone' => '+94770000123',
            'registration_no' => $registrationNo,
        ]);

        $service = Service::query()->firstOrCreate(
            ['key' => 'channeling'],
            ['name' => 'Specialist Channeling', 'bill_price' => 2500, 'system_price' => 500],
        );

        DoctorAvailability::query()->create([
            'doctor_id' => $doctor->id,
            'date' => $date,
            'time' => '09:00:00',
            'seats' => 5,
            'available_seats' => 4,
            'status' => DoctorAvailabilityStatus::ACTIVE->value,
        ]);

        $bill = Bill::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'date' => $date,
            'status' => BillStatus::BOOKED->value,
            'payment_type' => PaymentType::CASH->value,
            'shift' => 'morning',
            'bill_amount' => 2500,
            'system_amount' => 500,
            'appointment_type' => $service->name,
        ]);

        $bill->billItems()->create([
            'service_id' => $service->id,
            'bill_amount' => 2500,
            'system_amount' => 500,
        ]);

        DailyPatientQueue::query()->create([
            'bill_id' => $bill->id,
            'doctor_id' => $doctor->id,
            'queue_date' => $date,
            'queue_number' => 1,
            'order_number' => 1,
        ]);

        return $bill->load(['patient', 'doctor.specialty', 'billItems.service', 'dailyPatientQueue']);
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

<?php
namespace Tests\Feature\Appointments;

use App\Core\Auth;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
    }

    public function test_booking_an_appointment(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $this->simulateLogin($patient['user']['id'], 'patient');

        $result = AppointmentService::book([
            'patient_id' => $patient['patient']['id'],
            'doctor_id' => $doctor['doctor']['id'],
            'appointment_date' => date('Y-m-d', strtotime('+3 days')),
            'appointment_time' => '14:00:00',
            'type' => 'in-person',
            'reason' => 'Annual checkup',
        ]);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('id', $result);

        $appointment = Appointment::find($result['id']);
        $this->assertNotNull($appointment);
        $this->assertEquals('pending', $appointment['status']);
    }

    public function test_viewing_appointments(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $this->simulateLogin($patient['user']['id'], 'patient');

        $appt1 = AppointmentService::book([
            'patient_id' => $patient['patient']['id'],
            'doctor_id' => $doctor['doctor']['id'],
            'appointment_date' => date('Y-m-d', strtotime('+1 day')),
            'appointment_time' => '09:00:00',
        ]);

        $appt2 = AppointmentService::book([
            'patient_id' => $patient['patient']['id'],
            'doctor_id' => $doctor['doctor']['id'],
            'appointment_date' => date('Y-m-d', strtotime('+2 days')),
            'appointment_time' => '10:00:00',
        ]);

        $appointments = Appointment::getPatientAppointments($patient['patient']['id']);
        $this->assertCount(2, $appointments);
    }

    public function test_cancelling_appointment(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $this->simulateLogin($patient['user']['id'], 'patient');

        $result = AppointmentService::book([
            'patient_id' => $patient['patient']['id'],
            'doctor_id' => $doctor['doctor']['id'],
            'appointment_date' => date('Y-m-d', strtotime('+5 days')),
            'appointment_time' => '11:00:00',
        ]);

        $this->assertTrue($result['success']);

        $cancelled = AppointmentService::cancel($result['id'], 'Patient is unavailable');
        $this->assertTrue($cancelled);

        $appointment = Appointment::find($result['id']);
        $this->assertEquals('cancelled', $appointment['status']);
    }

    public function test_appointment_validation(): void
    {
        // Missing fields should fail
        $result = AppointmentService::book([
            'patient_id' => 1,
            // missing doctor_id
            'appointment_date' => '',
            'appointment_time' => '',
        ]);

        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['errors']);
    }

    public function test_doctor_can_confirm_appointment(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $result = AppointmentService::book([
            'patient_id' => $patient['patient']['id'],
            'doctor_id' => $doctor['doctor']['id'],
            'appointment_date' => date('Y-m-d', strtotime('+2 days')),
            'appointment_time' => '15:00:00',
        ]);

        $this->assertTrue($result['success']);

        $confirmed = AppointmentService::confirm($result['id']);
        $this->assertTrue($confirmed);

        $appointment = Appointment::find($result['id']);
        $this->assertEquals('confirmed', $appointment['status']);
    }

    public function test_doctor_can_complete_appointment(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $result = AppointmentService::book([
            'patient_id' => $patient['patient']['id'],
            'doctor_id' => $doctor['doctor']['id'],
            'appointment_date' => date('Y-m-d', strtotime('+1 day')),
            'appointment_time' => '09:30:00',
        ]);

        AppointmentService::confirm($result['id']);
        AppointmentService::complete($result['id']);

        $appointment = Appointment::find($result['id']);
        $this->assertEquals('completed', $appointment['status']);
    }

    public function test_cannot_book_duplicate_time_slot(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        // Use today's date so the availability check kicks in (strtotime <= today)
        $date = date('Y-m-d');

        $first = AppointmentService::book([
            'patient_id' => $patient['patient']['id'],
            'doctor_id' => $doctor['doctor']['id'],
            'appointment_date' => $date,
            'appointment_time' => '10:00:00',
        ]);

        $this->assertTrue($first['success']);

        $second = AppointmentService::book([
            'patient_id' => $patient['patient']['id'],
            'doctor_id' => $doctor['doctor']['id'],
            'appointment_date' => $date,
            'appointment_time' => '10:00:00',
        ]);

        $this->assertFalse($second['success']);
        $this->assertContains('This time slot is already booked', $second['errors']);
    }

    public function test_get_available_slots(): void
    {
        $doctor = $this->createDoctor();

        $slots = AppointmentService::getAvailableSlots(
            $doctor['doctor']['id'],
            date('Y-m-d', strtotime('+7 days'))
        );

        $this->assertNotEmpty($slots);
        foreach ($slots as $slot) {
            $this->assertMatchesRegularExpression('/^\d{2}:\d{2}:00$/', $slot);
        }
    }
}

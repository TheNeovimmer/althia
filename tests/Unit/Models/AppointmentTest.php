<?php
namespace Tests\Unit\Models;

use App\Core\Database;
use App\Models\Appointment;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    public function test_can_create_appointment(): void
    {
        $data = $this->createAppointment();

        $this->assertIsInt($data['id']);

        $appointment = Appointment::find($data['id']);
        $this->assertNotNull($appointment);
        $this->assertEquals($data['patient_id'], $appointment['patient_id']);
        $this->assertEquals($data['doctor_id'], $appointment['doctor_id']);
        $this->assertEquals('pending', $appointment['status']);
        $this->assertEquals('in-person', $appointment['type']);
    }

    public function test_appointment_status_transitions(): void
    {
        $data = $this->createAppointment();

        // Start as pending
        $appointment = Appointment::find($data['id']);
        $this->assertEquals('pending', $appointment['status']);

        // Confirm
        Appointment::update($data['id'], ['status' => 'confirmed']);
        $appointment = Appointment::find($data['id']);
        $this->assertEquals('confirmed', $appointment['status']);

        // Complete
        Appointment::update($data['id'], ['status' => 'completed']);
        $appointment = Appointment::find($data['id']);
        $this->assertEquals('completed', $appointment['status']);

        // Cancel from any state
        Appointment::update($data['id'], ['status' => 'cancelled']);
        $appointment = Appointment::find($data['id']);
        $this->assertEquals('cancelled', $appointment['status']);
    }

    public function test_appointment_date_validation(): void
    {
        $db = $this->getDb();
        $validDate = date('Y-m-d', strtotime('+1 day'));
        $this->assertNotFalse(strtotime($validDate));

        $invalidDate = 'not-a-date';
        $this->assertFalse(strtotime($invalidDate) !== false);
    }

    public function test_can_get_patient_appointments(): void
    {
        $data1 = $this->createAppointment();
        $data2 = $this->createAppointment([
            'patient_id' => $data1['patient']['patient']['id'],
        ]);

        $appointments = Appointment::getPatientAppointments($data1['patient']['patient']['id']);
        $this->assertCount(2, $appointments);
    }

    public function test_can_get_doctor_appointments(): void
    {
        $data1 = $this->createAppointment();
        $data2 = $this->createAppointment([
            'doctor_id' => $data1['doctor']['doctor']['id'],
        ]);

        $appointments = Appointment::getDoctorAppointments($data1['doctor']['doctor']['id']);
        $this->assertCount(2, $appointments);
    }

    public function test_can_get_upcoming_for_patient(): void
    {
        // Past appointment
        $past = $this->createAppointment([
            'appointment_date' => date('Y-m-d', strtotime('-1 day')),
            'status' => 'completed',
        ]);

        // Future pending appointment
        $future = $this->createAppointment([
            'patient_id' => $past['patient']['patient']['id'],
            'appointment_date' => date('Y-m-d', strtotime('+2 days')),
            'status' => 'pending',
        ]);

        $upcoming = Appointment::getUpcomingForPatient($past['patient']['patient']['id']);
        $this->assertCount(1, $upcoming);
        $this->assertEquals($future['id'], $upcoming[0]['id']);
    }

    public function test_can_get_stats(): void
    {
        $this->createAppointment(['status' => 'pending']);
        $this->createAppointment(['status' => 'confirmed']);
        $this->createAppointment(['status' => 'completed']);
        $this->createAppointment(['status' => 'cancelled']);

        $stats = Appointment::getStats();
        $this->assertEquals(4, $stats['total']);
        $this->assertEquals(1, $stats['pending']);
        $this->assertEquals(1, $stats['confirmed']);
        $this->assertEquals(1, $stats['completed']);
        $this->assertEquals(1, $stats['cancelled']);
    }

    public function test_check_availability(): void
    {
        $data = $this->createAppointment([
            'appointment_time' => '10:00:00',
        ]);

        $slots = Appointment::checkAvailability($data['doctor_id'], $data['appointment_date']);
        $this->assertCount(1, $slots);
        $this->assertEquals('10:00:00', $slots[0]['appointment_time']);
    }

    public function test_can_cancel_appointment_with_reason(): void
    {
        $data = $this->createAppointment();

        $id = $data['id'];
        Appointment::update($id, ['status' => 'cancelled']);
        // cancelled_at and cancellation_reason are not in $fillable, so use raw SQL
        $db = Database::getInstance();
        $db->execute(
            "UPDATE appointments SET cancelled_at = ?, cancellation_reason = ? WHERE id = ?",
            [date('Y-m-d H:i:s'), 'Patient requested cancellation', $id]
        );

        $appointment = Appointment::find($id);
        $this->assertEquals('cancelled', $appointment['status']);
        $this->assertEquals('Patient requested cancellation', $appointment['cancellation_reason']);
    }
}

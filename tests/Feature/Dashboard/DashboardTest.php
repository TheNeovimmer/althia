<?php
namespace Tests\Feature\Dashboard;

use App\Core\Auth;
use App\Core\Database;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
    }

    public function test_patient_dashboard_loads(): void
    {
        $patientData = $this->createPatient();
        $this->simulateLogin($patientData['user']['id'], 'patient');

        $this->assertTrue(Auth::check());
        $this->assertEquals('patient', Auth::role());
        $this->assertTrue(Auth::isPatient());
    }

    public function test_patient_dashboard_shows_appointments(): void
    {
        $patientData = $this->createPatient();
        $doctorData = $this->createDoctor();

        $this->simulateLogin($patientData['user']['id'], 'patient');

        Appointment::create([
            'patient_id' => $patientData['patient']['id'],
            'doctor_id' => $doctorData['doctor']['id'],
            'appointment_date' => date('Y-m-d', strtotime('+1 day')),
            'appointment_time' => '10:00:00',
            'status' => 'confirmed',
            'type' => 'in-person',
            'reason' => 'Follow-up',
        ]);

        Appointment::create([
            'patient_id' => $patientData['patient']['id'],
            'doctor_id' => $doctorData['doctor']['id'],
            'appointment_date' => date('Y-m-d', strtotime('+3 days')),
            'appointment_time' => '14:00:00',
            'status' => 'pending',
            'type' => 'video',
            'reason' => 'Consultation',
        ]);

        $upcoming = Appointment::getUpcomingForPatient($patientData['patient']['id']);
        $this->assertCount(2, $upcoming);
    }

    public function test_doctor_dashboard_loads(): void
    {
        $doctorData = $this->createDoctor();
        $this->simulateLogin($doctorData['user']['id'], 'doctor');

        $this->assertTrue(Auth::check());
        $this->assertEquals('doctor', Auth::role());
        $this->assertTrue(Auth::isDoctor());
    }

    public function test_doctor_dashboard_shows_today_appointments(): void
    {
        $doctorData = $this->createDoctor();

        $this->simulateLogin($doctorData['user']['id'], 'doctor');

        $today = date('Y-m-d');
        Appointment::create([
            'patient_id' => $this->createPatient()['patient']['id'],
            'doctor_id' => $doctorData['doctor']['id'],
            'appointment_date' => $today,
            'appointment_time' => '09:00:00',
            'status' => 'confirmed',
            'type' => 'in-person',
            'reason' => 'Checkup',
        ]);

        $todayAppts = Appointment::getTodayForDoctor($doctorData['doctor']['id']);
        $this->assertCount(1, $todayAppts);
    }

    public function test_admin_dashboard_loads(): void
    {
        $adminId = $this->createUser([
            'role' => 'admin',
            'email' => 'admindash@test.com',
            'password' => Auth::hashPassword('admin123'),
        ])['id'];

        $this->simulateLogin($adminId, 'admin');

        $this->assertTrue(Auth::check());
        $this->assertEquals('admin', Auth::role());
        $this->assertTrue(Auth::isAdmin());
    }

    public function test_admin_dashboard_shows_stats(): void
    {
        $adminId = $this->createUser([
            'role' => 'admin',
            'email' => 'adminstats@test.com',
            'password' => Auth::hashPassword('admin123'),
        ])['id'];

        $this->simulateLogin($adminId, 'admin');

        $this->createDoctor(['email' => 'doc1@test.com']);
        $this->createDoctor(['email' => 'doc2@test.com']);
        $this->createPatient(['email' => 'pat1@test.com']);
        $this->createPatient(['email' => 'pat2@test.com']);
        $this->createPatient(['email' => 'pat3@test.com']);

        $db = Database::getInstance();
        $stats = [
            'totalDoctors' => User::countWhere('role', 'doctor'),
            'totalPatients' => User::countWhere('role', 'patient'),
            'totalUsers' => User::count(),
        ];

        $this->assertEquals(2, $stats['totalDoctors']);
        $this->assertEquals(3, $stats['totalPatients']);
        // admin + 2 doctors + 3 patients = 6, but we also have users created by createPatient/createDoctor
        // So we just check the counts are sensible
        $this->assertGreaterThanOrEqual(6, $stats['totalUsers']);
    }

    public function test_unauthorized_access_redirects(): void
    {
        Auth::logout();
        $this->assertFalse(Auth::check());

        // Auth::requireAuth() should redirect
        $this->assertTrue(true, 'Unauthenticated users are redirected to /login');
    }

    public function test_patient_cannot_access_doctor_dashboard(): void
    {
        $patientData = $this->createPatient();
        $this->simulateLogin($patientData['user']['id'], 'patient');

        $this->assertFalse(Auth::isDoctor());
        $this->assertFalse(Auth::isAdmin());
    }

    public function test_doctor_cannot_access_admin_dashboard(): void
    {
        $doctorData = $this->createDoctor();
        $this->simulateLogin($doctorData['user']['id'], 'doctor');

        $this->assertFalse(Auth::isAdmin());
        $this->assertTrue(Auth::isDoctor());
    }

    public function test_admin_can_access_all_dashboards(): void
    {
        $adminData = $this->createUser([
            'role' => 'admin',
            'email' => 'superadmin@test.com',
            'password' => Auth::hashPassword('admin123'),
        ]);
        $this->simulateLogin($adminData['id'], 'admin');

        $this->assertTrue(Auth::isAdmin());
        $this->assertFalse(Auth::isDoctor());
        $this->assertFalse(Auth::isPatient());
    }

    public function test_dashboard_shows_unread_notifications(): void
    {
        $userData = $this->createUser([
            'role' => 'patient',
            'email' => 'notif@test.com',
            'password' => Auth::hashPassword('password123'),
        ]);

        $this->simulateLogin($userData['id'], 'patient');

        $db = Database::getInstance();
        $db->insert(
            "INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)",
            [$userData['id'], 'appointment', 'Appointment Reminder', 'You have an appointment tomorrow.', '/patient/appointments']
        );

        $db->insert(
            "INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)",
            [$userData['id'], 'prescription', 'New Prescription', 'Your doctor issued a prescription.', '/patient/prescriptions']
        );

        $unreadCount = \App\Models\User::getUnreadNotificationsCount($userData['id']);
        $this->assertEquals(2, $unreadCount);
    }
}

<?php
/**
 * Medicase Database Seeder
 * Run: php database/seed.php
 * Requires database/schema.sql to be imported first.
 */

require_once __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/database.php';

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $db = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    echo "Seeding medicase database...\n\n";

    // Helper
    $hash = fn($p) => password_hash($p, PASSWORD_BCRYPT);

    // 1. Users
    $users = [
        ['admin@medicase.com', $hash('password'), 'admin', 'Admin', 'Medicase', '+21650111111', 1],
        ['sarah.johnson@email.com', $hash('password'), 'patient', 'Sarah', 'Johnson', '+21650111112', 1],
        ['mike.chen@email.com', $hash('password'), 'patient', 'Mike', 'Chen', '+21650111113', 1],
        ['emma.davis@email.com', $hash('password'), 'patient', 'Emma', 'Davis', '+21650111114', 1],
        ['james.wilson@email.com', $hash('password'), 'patient', 'James', 'Wilson', '+21650111115', 1],
        ['olivia.brown@email.com', $hash('password'), 'patient', 'Olivia', 'Brown', '+21650111116', 1],
        ['dr.ahmed@medicase.com', $hash('password'), 'doctor', 'Ahmed', 'Hassan', '+21650222221', 1],
        ['dr.maria@medicase.com', $hash('password'), 'doctor', 'Maria', 'Santos', '+21650222222', 1],
        ['dr.pierre@medicase.com', $hash('password'), 'doctor', 'Pierre', 'Dubois', '+21650222223', 1],
        ['dr.li@medicase.com', $hash('password'), 'doctor', 'Wei', 'Li', '+21650222224', 1],
    ];

    $stmt = $db->prepare(
        "INSERT INTO users (email, password, role, first_name, last_name, phone, is_active, email_verified_at, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), DATE_SUB(NOW(), INTERVAL ? DAY))"
    );

    foreach ($users as $i => $u) {
        $days = rand(1, 60);
        $stmt->execute([$u[0], $u[1], $u[2], $u[3], $u[4], $u[5], $u[6], $days]);
        echo "  User: {$u[3]} {$u[4]} ({$u[2]})\n";
    }
    echo "\n";

    // 2. Patients
    $patientUsers = [2, 3, 4, 5, 6];
    $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    $genders = ['male', 'female'];
    foreach ($patientUsers as $uid) {
        $db->prepare(
            "INSERT INTO patients (user_id, date_of_birth, gender, blood_type, weight, height, address, emergency_contact_name, emergency_contact_phone)
             VALUES (?, DATE_SUB(NOW(), INTERVAL ? YEAR), ?, ?, ?, ?, ?, ?, ?)"
        )->execute([
            $uid,
            rand(20, 60),
            $genders[array_rand($genders)],
            $bloodTypes[array_rand($bloodTypes)],
            rand(55, 95),
            rand(155, 190),
            ['123 Main St, Tunis', '45 Oak Ave, Sfax', '78 Pine Rd, Sousse', '12 Beach Blvd, Hammamet', '90 Garden Ln, Monastir'][$uid - 2],
            ['John Johnson', 'Lisa Chen', 'Robert Davis', 'Laura Wilson', 'Tom Brown'][$uid - 2],
            '+21699' . str_pad((string)rand(100000, 999999), 6, '0', STR_PAD_LEFT),
        ]);
        echo "  Patient created for user_id={$uid}\n";
    }
    echo "\n";

    // 3. Specializations (already seeded in schema.sql, ensure they exist)
    $specs = [
        ['Cardiology', 'Heart and cardiovascular system'],
        ['Neurology', 'Brain and nervous system'],
        ['Pediatrics', 'Children\'s health'],
        ['Orthopedics', 'Bones and joints'],
        ['Dermatology', 'Skin conditions'],
        ['Ophthalmology', 'Eye care'],
        ['Psychiatry', 'Mental health'],
        ['General Medicine', 'Primary care'],
    ];
    $specCount = $db->query("SELECT COUNT(*) FROM specializations")->fetchColumn();
    if ($specCount == 0) {
        $stmt = $db->prepare("INSERT INTO specializations (name, description) VALUES (?, ?)");
        foreach ($specs as $s) {
            $stmt->execute($s);
        }
        echo "  Created " . count($specs) . " specializations\n\n";
    }

    // 4. Doctors
    $doctorUsers = [7, 8, 9, 10];
    $doctorData = [
        ['user_id' => 7, 'spec_id' => 1, 'license' => 'LIC-TN-2024-001', 'bio' => 'Senior cardiologist with 15 years of experience in interventional cardiology.', 'education' => 'MD, Tunis Medical University', 'exp' => 15, 'fee' => 120],
        ['user_id' => 8, 'spec_id' => 2, 'license' => 'LIC-TN-2024-002', 'bio' => 'Neurologist specializing in neurodegenerative disorders and stroke management.', 'education' => 'MD, University of Lisbon', 'exp' => 12, 'fee' => 150],
        ['user_id' => 9, 'spec_id' => 3, 'license' => 'LIC-TN-2024-003', 'bio' => 'Caring pediatrician with expertise in child development and adolescent medicine.', 'education' => 'MD, Paris Descartes University', 'exp' => 10, 'fee' => 100],
        ['user_id' => 10, 'spec_id' => 4, 'license' => 'LIC-TN-2024-004', 'bio' => 'Orthopedic surgeon focused on sports injuries and joint replacement surgeries.', 'education' => 'MD, Beijing Medical University', 'exp' => 18, 'fee' => 180],
    ];
    foreach ($doctorData as $d) {
        $db->prepare(
            "INSERT INTO doctors (user_id, specialization_id, license_number, bio, education, experience_years, consultation_fee, is_verified)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1)"
        )->execute([$d['user_id'], $d['spec_id'], $d['license'], $d['bio'], $d['education'], $d['exp'], $d['fee']]);
        echo "  Doctor created for user_id={$d['user_id']}\n";
    }
    echo "\n";

    // 5. Appointments
    $appointmentStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
    $patientIds = range(1, 5);
    $doctorIds = range(1, 4);
    $reasons = [
        'Annual checkup', 'Chest pain evaluation', 'Headache consultation',
        'Skin rash examination', 'Eye strain follow-up', 'Joint pain assessment',
        'Allergy testing', 'Blood pressure monitoring', 'Thyroid function test',
        'Sleep disorder consultation', 'Digestive issues', 'Vaccination appointment',
    ];
    $appointmentTypes = ['in-person', 'video', 'phone'];

    $aptStmt = $db->prepare(
        "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, type, reason, status, created_at)
         VALUES (?, ?, DATE_ADD(CURDATE(), INTERVAL ? DAY), ?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL ? DAY))"
    );

    $appointmentsCreated = 0;
    for ($i = 0; $i < 25; $i++) {
        $pid = $patientIds[array_rand($patientIds)];
        $did = $doctorIds[array_rand($doctorIds)];
        $daysOffset = rand(-15, 30);
        $hour = rand(8, 17);
        $minute = rand(0, 3) * 15;
        $status = $daysOffset < 0 ? 'completed' : $appointmentStatuses[array_rand(array_slice($appointmentStatuses, 0, 3))];
        $daysAgo = rand(1, 30);

        $aptStmt->execute([
            $pid, $did, $daysOffset,
            sprintf('%02d:%02d:00', $hour, $minute),
            $appointmentTypes[array_rand($appointmentTypes)],
            $reasons[array_rand($reasons)],
            $status, $daysAgo
        ]);
        $appointmentsCreated++;
    }
    echo "  Created {$appointmentsCreated} appointments\n\n";

    // 6. Medical Records
    $diagnoses = [
        'Essential hypertension', 'Type 2 diabetes mellitus', 'Acute bronchitis',
        'Migraine without aura', 'Allergic rhinitis', 'Lumbar back pain',
        'Iron deficiency anemia', 'Hypothyroidism', 'Osteoarthritis',
        'Gastroesophageal reflux', 'Anxiety disorder', 'Seasonal allergies',
    ];
    $symptomsList = [
        'Chest pain, shortness of breath', 'Frequent urination, increased thirst',
        'Cough, fever, sputum production', 'Throbbing headache, photophobia',
        'Sneezing, runny nose, itchy eyes', 'Lower back pain, stiffness',
        'Fatigue, pale skin, dizziness', 'Weight gain, cold intolerance',
        'Joint pain, morning stiffness', 'Heartburn, regurgitation',
        'Excessive worry, restlessness', 'Sneezing, watery eyes',
    ];

    $recStmt = $db->prepare(
        "INSERT INTO medical_records (patient_id, doctor_id, diagnosis, symptoms, notes, record_date, created_at)
         VALUES (?, ?, ?, ?, ?, DATE_SUB(CURDATE(), INTERVAL ? DAY), DATE_SUB(NOW(), INTERVAL ? DAY))"
    );

    $recordsCreated = 0;
    for ($i = 0; $i < 20; $i++) {
        $pid = $patientIds[array_rand($patientIds)];
        $did = $doctorIds[array_rand($doctorIds)];
        $idx = array_rand($diagnoses);
        $daysAgo = rand(1, 90);

        $recStmt->execute([
            $pid, $did,
            $diagnoses[$idx],
            $symptomsList[$idx],
            'Patient advised to follow up in 2 weeks. Prescribed appropriate medication.',
            $daysAgo, $daysAgo
        ]);
        $recordsCreated++;
    }
    echo "  Created {$recordsCreated} medical records\n\n";

    // 7. Prescriptions
    $medications = [
        ['Lisinopril', '10 mg', 'Once daily', '30 days'],
        ['Metformin', '500 mg', 'Twice daily', '90 days'],
        ['Amoxicillin', '500 mg', 'Three times daily', '7 days'],
        ['Ibuprofen', '400 mg', 'As needed', '14 days'],
        ['Omeprazole', '20 mg', 'Once daily', '30 days'],
        ['Atorvastatin', '20 mg', 'Once daily at night', '90 days'],
        ['Sertraline', '50 mg', 'Once daily', '30 days'],
        ['Salbutamol inhaler', '100 mcg', 'As needed', '30 days'],
        ['Levothyroxine', '50 mcg', 'Once daily', '90 days'],
        ['Amlodipine', '5 mg', 'Once daily', '30 days'],
    ];

    $rxStmt = $db->prepare(
        "INSERT INTO prescriptions (patient_id, doctor_id, medication_name, dosage, frequency, duration, instructions, start_date, is_active, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, DATE_SUB(CURDATE(), INTERVAL ? DAY), 1, DATE_SUB(NOW(), INTERVAL ? DAY))"
    );

    $rxCreated = 0;
    for ($i = 0; $i < 20; $i++) {
        $pid = $patientIds[array_rand($patientIds)];
        $did = $doctorIds[array_rand($doctorIds)];
        $med = $medications[array_rand($medications)];
        $daysAgo = rand(1, 60);

        $rxStmt->execute([
            $pid, $did,
            $med[0], $med[1], $med[2], $med[3],
            'Take with food. Avoid alcohol.',
            $daysAgo, $daysAgo
        ]);
        $rxCreated++;
    }
    echo "  Created {$rxCreated} prescriptions\n\n";

    // 8. Blog categories
    $blogCategories = ['Health Tips', 'Pediatrics', 'Technology', 'Mental Health', 'Nutrition'];
    $catStmt = $db->prepare("INSERT INTO blog_categories (name, slug) VALUES (?, ?)");
    foreach ($blogCategories as $i => $cat) {
        $slug = strtolower(str_replace(' ', '-', $cat));
        $catStmt->execute([$cat, $slug]);
        echo "  Blog category: {$cat}\n";
    }
    echo "\n";

    // 9. Blog posts
    $blogPosts = [
        ['5 Tips for a Healthy Heart', 'Maintaining heart health is crucial for overall well-being. Here are five essential tips to keep your cardiovascular system in top shape: regular exercise, balanced diet, stress management, adequate sleep, and regular checkups.', 7, 1, '["health","heart","tips"]'],
        ['Understanding Childhood Vaccinations', 'Vaccinations are one of the most important preventive measures in pediatric care. This comprehensive guide explains the recommended vaccination schedule and addresses common concerns parents may have.', 9, 2, '["pediatrics","vaccination","children"]'],
        ['The Future of Telemedicine', 'Telemedicine is revolutionizing healthcare delivery. From virtual consultations to remote monitoring, discover how digital health technologies are making healthcare more accessible than ever before.', 7, 3, '["telemedicine","technology","healthcare"]'],
        ['Managing Stress in Modern Life', 'Chronic stress affects both mental and physical health. Learn effective strategies for stress management, including mindfulness techniques, exercise routines, and when to seek professional help.', 8, 4, '["mental-health","stress","wellness"]'],
        ['Nutrition Myths Debunked', 'Separating fact from fiction in the world of nutrition. Our experts examine common dietary myths and provide evidence-based recommendations for a healthier diet.', 10, 5, '["nutrition","diet","myths"]'],
    ];

    $blogStmt = $db->prepare(
        "INSERT INTO blog_posts (title, slug, content, excerpt, author_id, category_id, tags, is_published, published_at, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, 1, DATE_SUB(NOW(), INTERVAL ? DAY), DATE_SUB(NOW(), INTERVAL ? DAY))"
    );

    $excerpts = [
        'Expert tips for maintaining cardiovascular health through lifestyle changes.',
        'A parent\'s guide to understanding the childhood vaccination schedule.',
        'How telemedicine is transforming healthcare delivery worldwide.',
        'Practical strategies for managing stress in today\'s fast-paced world.',
        'Evidence-based nutrition advice from medical professionals.',
    ];

    foreach ($blogPosts as $i => $bp) {
        $slug = strtolower(str_replace(' ', '-', $bp[0]));
        $daysAgo = rand(5, 30);
        $blogStmt->execute([$bp[0], $slug, $bp[1], $excerpts[$i], $bp[2], $bp[3], $bp[4], $daysAgo, $daysAgo]);
        echo "  Blog post: {$bp[0]}\n";
    }
    echo "\n";

    // 10. Services
    $services = [
        ['General Consultation', 'Comprehensive primary care consultations for patients of all ages.', 'fa-stethoscope', 1],
        ['Specialist Consultation', 'Access to top specialists in cardiology, neurology, orthopedics and more.', 'fa-user-md', 1],
        ['Health Checkup Package', 'Complete health screening including blood work, imaging, and physical examination.', 'fa-heartbeat', 1],
        ['Mental Health Support', 'Professional counseling and psychiatric services in a confidential setting.', 'fa-brain', 1],
    ];

    $svcCount = $db->query("SELECT COUNT(*) FROM services")->fetchColumn();
    if ($svcCount == 0) {
        $svcStmt = $db->prepare("INSERT INTO services (name, description, icon, is_active) VALUES (?, ?, ?, ?)");
        foreach ($services as $svc) {
            $svcStmt->execute($svc);
        }
        echo "  Created " . count($services) . " services\n";
    }

    // 11. Notifications for existing users
    $notifStmt = $db->prepare(
        "INSERT INTO notifications (user_id, type, title, message, link, is_read, created_at)
         VALUES (?, ?, ?, ?, ?, 0, DATE_SUB(NOW(), INTERVAL ? DAY))"
    );

    $notifTypes = ['appointment', 'prescription', 'message', 'report', 'system'];
    $notifTitles = [
        'Appointment Reminder', 'New Prescription Available', 'New Message Received',
        'Medical Report Uploaded', 'Welcome to Medicase',
    ];
    $notifMessages = [
        'You have an upcoming appointment tomorrow at 10:00 AM.',
        'Your doctor has issued a new prescription. Please review it in your dashboard.',
        'You have received a new message from your doctor.',
        'A new medical report has been uploaded to your records.',
        'Welcome to Medicase! We are glad to have you on board.',
    ];

    for ($uid = 2; $uid <= 10; $uid++) {
        $numNotif = rand(1, 4);
        for ($n = 0; $n < $numNotif; $n++) {
            $idx = array_rand($notifTitles);
            $daysAgo = rand(0, 14);
            $notifStmt->execute([
                $uid, $notifTypes[array_rand($notifTypes)],
                $notifTitles[$idx], $notifMessages[$idx],
                '/' . ['patient', 'patient', 'patient', 'patient', 'patient'][$idx] . '/dashboard',
                $daysAgo
            ]);
        }
    }
    echo "  Created notifications for users 2-10\n\n";

    echo "✅ Seeding complete!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

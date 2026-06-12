<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <h1>Patient Profile</h1>
            <a href="/doctor/patients" class="btn btn-outline btn-sm">Back to Patients</a>
        </div>

        <div class="profile-layout">
            <div class="profile-card">
                <div class="profile-avatar">
                    <span class="avatar-initials lg"><?= strtoupper(substr($patient['first_name'], 0, 1) . substr($patient['last_name'], 0, 1)) ?></span>
                </div>
                <h3><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h3>
                <span class="profile-email"><?= htmlspecialchars($patient['email']) ?></span>
                <span class="patient-meta">DOB: <?= htmlspecialchars($patient['date_of_birth'] ?? 'N/A') ?></span>
                <?php if ($patient['blood_type']): ?>
                    <span class="blood-badge">Blood Type: <?= htmlspecialchars($patient['blood_type']) ?></span>
                <?php endif; ?>
            </div>

            <div class="profile-details">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Medical Records</h3>
                        <a href="/doctor/records/create/<?= $patient['id'] ?>" class="btn btn-primary btn-sm">Add Record</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($records)): ?>
                            <?php foreach ($records as $r): ?>
                                <div class="record-mini">
                                    <div class="record-mini-header">
                                        <strong><?= htmlspecialchars($r['diagnosis'] ?? 'Record') ?></strong>
                                        <span><?= date('M j, Y', strtotime($r['record_date'])) ?></span>
                                    </div>
                                    <p><?= htmlspecialchars(truncate($r['notes'] ?? '', 150)) ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No records yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Prescription History</h3>
                        <a href="/doctor/prescriptions/create/<?= $patient['id'] ?>" class="btn btn-primary btn-sm">Prescribe</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($prescriptions)): ?>
                            <?php foreach ($prescriptions as $rx): ?>
                                <div class="rx-mini">
                                    <strong><?= htmlspecialchars($rx['medication_name']) ?></strong>
                                    <span><?= htmlspecialchars($rx['dosage']) ?> — <?= htmlspecialchars($rx['frequency']) ?></span>
                                    <span class="rx-date"><?= date('M j, Y', strtotime($rx['created_at'])) ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No prescriptions yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Appointment History</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($appointmentHistory)): ?>
                            <table class="data-table data-table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointmentHistory as $apt): ?>
                                        <tr>
                                            <td><?= date('M j, Y', strtotime($apt['appointment_date'])) ?></td>
                                            <td><?= date('H:i', strtotime($apt['appointment_time'])) ?></td>
                                            <td><span class="apt-status <?= $apt['status'] ?>"><?= ucfirst($apt['status']) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">No appointments yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

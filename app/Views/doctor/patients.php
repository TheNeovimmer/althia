<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <h1>My Patients</h1>
        </div>

        <?php if (!empty($patients)): ?>
            <div class="patients-grid">
                <?php foreach ($patients as $p): ?>
                    <div class="patient-card">
                        <div class="patient-avatar">
                            <span class="avatar-initials"><?= strtoupper(substr($p['first_name'], 0, 1) . substr($p['last_name'], 0, 1)) ?></span>
                        </div>
                        <h3><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></h3>
                        <span class="patient-meta"><i class="fas fa-calendar"></i> DOB: <?= htmlspecialchars($p['date_of_birth'] ?? 'N/A') ?></span>
                        <?php if ($p['blood_type']): ?>
                            <span class="patient-meta blood-badge"><?= htmlspecialchars($p['blood_type']) ?></span>
                        <?php endif; ?>
                        <div class="patient-actions">
                            <a href="/doctor/patients/<?= $p['id'] ?>" class="btn btn-primary btn-sm">View Profile</a>
                            <a href="/doctor/records/create/<?= $p['id'] ?>" class="btn btn-outline btn-sm">Add Record</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>No patients yet</h3>
                <p>Patients will appear here after their first appointment with you.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <h1>Prescriptions</h1>
            <a href="/doctor/prescriptions/create" class="btn btn-primary btn-sm">New Prescription</a>
        </div>

        <?= flash_message() ?>

        <?php if (!empty($prescriptions)): ?>
            <div class="prescriptions-list">
                <?php foreach ($prescriptions as $rx): ?>
                    <div class="prescription-card">
                        <div class="rx-header">
                            <div class="rx-doctor">
                                <i class="fas fa-user"></i>
                                <div>
                                    <strong><?= htmlspecialchars($rx['patient_first_name'] . ' ' . $rx['patient_last_name']) ?></strong>
                                    <span>Prescribed: <?= date('M j, Y', strtotime($rx['created_at'])) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="rx-details">
                            <div class="rx-medication">
                                <span class="rx-label">Medication</span>
                                <span class="rx-value"><?= htmlspecialchars($rx['medication_name']) ?></span>
                            </div>
                            <div class="rx-dosage">
                                <span class="rx-label">Dosage</span>
                                <span class="rx-value"><?= htmlspecialchars($rx['dosage']) ?></span>
                            </div>
                            <div class="rx-frequency">
                                <span class="rx-label">Frequency</span>
                                <span class="rx-value"><?= htmlspecialchars($rx['frequency']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-prescription-bottle"></i>
                <h3>No prescriptions yet</h3>
            </div>
        <?php endif; ?>
    </div>
</section>

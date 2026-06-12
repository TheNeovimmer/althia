<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <h1>My Prescriptions</h1>
        </div>

        <?php if (!empty($prescriptions)): ?>
            <div class="prescriptions-list">
                <?php foreach ($prescriptions as $rx): ?>
                    <div class="prescription-card">
                        <div class="rx-header">
                            <div class="rx-doctor">
                                <i class="fas fa-prescription"></i>
                                <div>
                                    <strong>Dr. <?= htmlspecialchars(($rx['doctor_first_name'] ?? '') . ' ' . ($rx['doctor_last_name'] ?? '')) ?></strong>
                                    <span>Prescribed: <?= date('M j, Y', strtotime($rx['created_at'])) ?></span>
                                </div>
                            </div>
                            <?php if ($rx['is_active']): ?>
                                <span class="rx-active">Active</span>
                            <?php else: ?>
                                <span class="rx-expired">Completed</span>
                            <?php endif; ?>
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
                            <div class="rx-duration">
                                <span class="rx-label">Duration</span>
                                <span class="rx-value"><?= htmlspecialchars($rx['duration'] ?? 'As needed') ?></span>
                            </div>
                        </div>
                        <?php if (!empty($rx['instructions'])): ?>
                            <div class="rx-notes">
                                <span class="rx-label">Instructions</span>
                                <p><?= htmlspecialchars($rx['instructions']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-prescription-bottle"></i>
                <h3>No prescriptions</h3>
                <p>Your doctor will prescribe medications as needed during your visits.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-prescription" style="color:var(--primary);margin-right:12px;"></i>New Prescription</h1>
                <p>Create a prescription for your patient</p>
            </div>
            <a href="/doctor/prescriptions" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <?= flash_message() ?>

        <div class="form-card form-card-lg">
            <form method="POST" action="/doctor/prescriptions/create">
                <?= csrf_field() ?>

                <div class="form-section">
                    <div class="form-section-title">Patient Information</div>

                    <div class="form-group">
                        <label for="patient_id" class="form-label"><i class="fas fa-user"></i> Patient</label>
                        <select id="patient_id" name="patient_id" class="form-select" required>
                            <option value="">Select patient</option>
                            <?php foreach ($patients as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= (isset($patientId) && $patientId == $p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Medication Details</div>

                    <div class="form-group">
                        <label for="medication_name" class="form-label"><i class="fas fa-pills"></i> Medication Name</label>
                        <input type="text" id="medication_name" name="medication_name" class="form-input" placeholder="Enter medication name" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="dosage" class="form-label"><i class="fas fa-weight-scale"></i> Dosage</label>
                            <input type="text" id="dosage" name="dosage" class="form-input" placeholder="e.g. 500mg" required>
                        </div>
                        <div class="form-group">
                            <label for="frequency" class="form-label"><i class="fas fa-clock"></i> Frequency</label>
                            <input type="text" id="frequency" name="frequency" class="form-input" placeholder="e.g. Twice daily" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="duration" class="form-label"><i class="fas fa-calendar-week"></i> Duration</label>
                        <input type="text" id="duration" name="duration" class="form-input" placeholder="e.g. 7 days">
                    </div>

                    <div class="form-group">
                        <label for="notes" class="form-label"><i class="fas fa-sticky-note"></i> Notes</label>
                        <textarea id="notes" name="notes" class="form-textarea" rows="3" placeholder="Additional instructions for the patient"></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="/doctor/prescriptions" class="btn btn-outline"><i class="fas fa-times"></i> Cancel</a>
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-prescription"></i> Create Prescription</button>
                </div>
            </form>
        </div>
    </div>
</section>

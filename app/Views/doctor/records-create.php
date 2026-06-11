<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-notes-medical" style="color:var(--primary);margin-right:12px;"></i>Add Medical Record</h1>
                <p>Document a patient's medical visit</p>
            </div>
            <a href="/doctor/patients" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <?= flash_message() ?>

        <div class="form-card form-card-lg">
            <form method="POST" action="/doctor/records/create" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="form-section">
                    <div class="form-section-title">Patient</div>

                    <div class="form-group">
                        <label for="patient_id" class="form-label"><i class="fas fa-user"></i> Select Patient</label>
                        <select id="patient_id" name="patient_id" class="form-select" required>
                            <option value="">Select patient</option>
                            <?php foreach ($patients as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= (isset($patientId) && $patientId == $p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Clinical Details</div>

                    <div class="form-group">
                        <label for="diagnosis" class="form-label"><i class="fas fa-stethoscope"></i> Diagnosis</label>
                        <input type="text" id="diagnosis" name="diagnosis" class="form-input" placeholder="Primary diagnosis" required>
                    </div>

                    <div class="form-group">
                        <label for="symptoms" class="form-label"><i class="fas fa-thermometer-half"></i> Symptoms</label>
                        <textarea id="symptoms" name="symptoms" class="form-textarea" rows="3" placeholder="Describe the patient's symptoms"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="notes" class="form-label"><i class="fas fa-align-left"></i> Notes</label>
                        <textarea id="notes" name="notes" class="form-textarea" rows="5" placeholder="Additional clinical notes"></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="/doctor/patients" class="btn btn-outline"><i class="fas fa-times"></i> Cancel</a>
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save Record</button>
                </div>
            </form>
        </div>
    </div>
</section>

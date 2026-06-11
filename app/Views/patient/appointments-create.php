<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-calendar-plus" style="color:var(--primary);margin-right:12px;"></i>Book Appointment</h1>
                <p>Schedule a consultation with a specialist</p>
            </div>
            <a href="/patient/appointments" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <?= flash_message() ?>

        <div class="form-card form-card-lg">
            <form method="POST" action="/patient/appointments/create">
                <?= csrf_field() ?>

                <div class="form-section">
                    <div class="form-section-title">Choose Your Doctor</div>

                    <div class="form-group">
                        <label for="doctor_id" class="form-label"><i class="fas fa-user-md"></i> Select Doctor</label>
                        <select id="doctor_id" name="doctor_id" class="form-select" required>
                            <option value="">Choose a doctor</option>
                            <?php foreach ($doctors as $doc): ?>
                                <option value="<?= $doc['id'] ?>">
                                    Dr. <?= htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']) ?>
                                    — <?= htmlspecialchars($doc['specialization_name'] ?? 'General') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Schedule</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="appointment_date" class="form-label"><i class="fas fa-calendar-day"></i> Date</label>
                            <input type="date" id="appointment_date" name="appointment_date" class="form-input" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="appointment_time" class="form-label"><i class="fas fa-clock"></i> Time</label>
                            <input type="time" id="appointment_time" name="appointment_time" class="form-input" required>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Details</div>

                    <div class="form-group">
                        <label for="reason" class="form-label"><i class="fas fa-notes-medical"></i> Reason for Visit</label>
                        <textarea id="reason" name="reason" class="form-textarea" rows="4" placeholder="Describe your symptoms or reason for appointment..." required></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="/patient/appointments" class="btn btn-outline"><i class="fas fa-times"></i> Cancel</a>
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-paper-plane"></i> Book Appointment</button>
                </div>
            </form>
        </div>
    </div>
</section>

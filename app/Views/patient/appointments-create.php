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
                    <div class="form-section-title">Choose Your Specialist</div>
                    <p class="form-section-desc">First pick a specialty, then select a doctor.</p>

                    <div class="form-group">
                        <label for="specialty" class="form-label"><i class="fas fa-stethoscope"></i> Specialty</label>
                        <select id="specialty" class="form-select">
                            <option value="">Select a specialty</option>
                            <?php foreach ($specializations as $spec): ?>
                                <option value="<?= htmlspecialchars($spec['name']) ?>">
                                    <?= htmlspecialchars($spec['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="doctor_id" class="form-label"><i class="fas fa-user-md"></i> Doctor</label>
                        <select id="doctor_id" name="doctor_id" class="form-select" required disabled>
                            <option value="">First select a specialty</option>
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

<script>
const doctors = <?= json_encode(array_map(fn($d) => [
    'id' => $d['id'],
    'name' => 'Dr. ' . $d['first_name'] . ' ' . $d['last_name'],
    'specialty' => $d['specialization_name'] ?? 'General',
    'avatar' => $d['avatar'] ?? '',
], $doctors)) ?>;

const specialtyEl = document.getElementById('specialty');
const doctorEl = document.getElementById('doctor_id');

specialtyEl.addEventListener('change', function() {
    const selected = this.value;
    doctorEl.innerHTML = '<option value="">Select a doctor</option>';
    doctorEl.disabled = !selected;

    if (!selected) return;

    const filtered = doctors.filter(d => d.specialty === selected);
    filtered.forEach(d => {
        const opt = document.createElement('option');
        opt.value = d.id;
        opt.textContent = d.name;
        doctorEl.appendChild(opt);
    });

    if (filtered.length === 0) {
        doctorEl.innerHTML = '<option value="">No doctors available for this specialty</option>';
    }
});
</script>

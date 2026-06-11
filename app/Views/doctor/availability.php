<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-clock" style="color:var(--primary);margin-right:12px;"></i>Availability</h1>
                <p>Set your weekly consultation hours</p>
            </div>
        </div>

        <?= flash_message() ?>

        <div class="form-card form-card-lg">
            <form method="POST" action="/doctor/availability">
                <?= csrf_field() ?>

                <div class="form-section">
                    <div class="form-section-title">Available Days</div>
                    <p class="form-section-desc">Select the days you are available for consultations.</p>

                    <?php $dayNames = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday']; ?>
                    <div class="day-selector-grid">
                        <?php foreach ($dayNames as $day): ?>
                            <label class="day-selector-card <?= in_array($day, $days) ? 'active' : '' ?>">
                                <input type="checkbox" name="days[]" value="<?= $day ?>" <?= in_array($day, $days) ? 'checked' : '' ?>>
                                <span class="day-name" style="text-transform:capitalize;"><?= $day === 'saturday' ? 'Saturday' : ($day === 'sunday' ? 'Sunday' :  ucfirst($day)) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Available Hours</div>
                    <p class="form-section-desc">Set your default working hours. Add multiple time slots if needed.</p>

                    <div id="hours-container">
                        <?php if (!empty($hours)): ?>
                            <?php foreach ($hours as $i => $slot): ?>
                                <div class="slot-row">
                                    <div class="form-group">
                                        <label class="form-label">Start Time</label>
                                        <input type="time" name="hours[<?= $i ?>][start]" value="<?= htmlspecialchars($slot['start'] ?? '09:00') ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">End Time</label>
                                        <input type="time" name="hours[<?= $i ?>][end]" value="<?= htmlspecialchars($slot['end'] ?? '17:00') ?>" required>
                                    </div>
                                    <button type="button" class="btn-remove-slot" onclick="this.closest('.slot-row').remove()" title="Remove"><i class="fas fa-times"></i></button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="slot-row">
                                <div class="form-group">
                                    <label class="form-label">Start Time</label>
                                    <input type="time" name="hours[0][start]" value="09:00" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">End Time</label>
                                    <input type="time" name="hours[0][end]" value="17:00" required>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="button" class="btn btn-outline btn-sm" onclick="addSlotRow()" style="margin-top:12px;">
                        <i class="fas fa-plus"></i> Add Time Slot
                    </button>
                </div>

                <div class="form-actions" style="border-top-color:var(--border);">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save Availability</button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
function addSlotRow() {
    const container = document.getElementById('hours-container');
    const idx = container.querySelectorAll('.slot-row').length;
    const row = document.createElement('div');
    row.className = 'slot-row';
    row.innerHTML = `
        <div class="form-group">
            <label class="form-label">Start Time</label>
            <input type="time" name="hours[${idx}][start]" value="09:00" required>
        </div>
        <div class="form-group">
            <label class="form-label">End Time</label>
            <input type="time" name="hours[${idx}][end]" value="17:00" required>
        </div>
        <button type="button" class="btn-remove-slot" onclick="this.closest('.slot-row').remove()" title="Remove"><i class="fas fa-times"></i></button>
    `;
    container.appendChild(row);
}
</script>

<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <h1>My Appointments</h1>
            <a href="/patient/appointments/create" class="btn btn-primary">Book New</a>
        </div>

        <?= flash_message() ?>

        <?php if (!empty($appointments)): ?>
            <div class="appointments-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Doctor</th>
                            <th>Specialization</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $apt): ?>
                            <tr>
                                <td><?= date('M j, Y', strtotime($apt['appointment_date'])) ?></td>
                                <td><?= date('H:i', strtotime($apt['appointment_time'])) ?></td>
                                <td>Dr. <?= htmlspecialchars($apt['doctor_first_name'] . ' ' . $apt['doctor_last_name']) ?></td>
                                <td><?= htmlspecialchars($apt['specialization_name'] ?? 'General') ?></td>
                                <td><span class="apt-status <?= $apt['status'] ?>"><?= ucfirst($apt['status']) ?></span></td>
                                <td>
                                    <?php if ($apt['status'] === 'pending' || $apt['status'] === 'confirmed'): ?>
                                        <form method="POST" action="/patient/appointments/<?= $apt['id'] ?>/cancel" style="display:inline" onsubmit="return confirm('Cancel this appointment?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h3>No appointments</h3>
                <p>Book your first appointment with one of our expert doctors.</p>
                <a href="/patient/appointments/create" class="btn btn-primary">Book Appointment</a>
            </div>
        <?php endif; ?>
    </div>
</section>

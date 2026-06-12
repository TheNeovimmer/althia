<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <h1>Appointments</h1>
        </div>

        <?= flash_message() ?>

        <?php if (!empty($appointments)): ?>
            <div class="appointments-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Patient</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $apt): ?>
                            <tr>
                                <td><?= date('M j, Y', strtotime($apt['appointment_date'])) ?></td>
                                <td><?= date('H:i', strtotime($apt['appointment_time'])) ?></td>
                                <td><?= htmlspecialchars($apt['patient_first_name'] . ' ' . $apt['patient_last_name']) ?></td>
                                <td><?= htmlspecialchars(truncate($apt['reason'] ?? '', 30)) ?></td>
                                <td><span class="apt-status <?= $apt['status'] ?>"><?= ucfirst($apt['status']) ?></span></td>
                                <td class="actions-cell">
                                    <?php if ($apt['status'] === 'pending' || $apt['status'] === 'confirmed'): ?>
                                        <form method="POST" action="/doctor/appointments/<?= $apt['id'] ?>/complete" style="display:inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-success">Complete</button>
                                        </form>
                                        <form method="POST" action="/doctor/appointments/<?= $apt['id'] ?>/cancel" style="display:inline">
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
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-calendar-check" style="margin-right:12px;color:var(--primary);"></i>Appointments</h1>
                <p>Manage all appointments across the platform</p>
            </div>
        </div>

        <?= flash_message() ?>

        <div class="status-tabs">
            <a href="/admin/appointments" class="status-tab <?= empty($statusFilter) ? 'active' : '' ?>">All <span class="count">(<?= $stats['total'] ?>)</span></a>
            <a href="/admin/appointments?status=pending" class="status-tab <?= $statusFilter === 'pending' ? 'active' : '' ?>">Pending <span class="count">(<?= $stats['pending'] ?>)</span></a>
            <a href="/admin/appointments?status=confirmed" class="status-tab <?= $statusFilter === 'confirmed' ? 'active' : '' ?>">Confirmed <span class="count">(<?= $stats['confirmed'] ?>)</span></a>
            <a href="/admin/appointments?status=completed" class="status-tab <?= $statusFilter === 'completed' ? 'active' : '' ?>">Completed <span class="count">(<?= $stats['completed'] ?>)</span></a>
            <a href="/admin/appointments?status=cancelled" class="status-tab <?= $statusFilter === 'cancelled' ? 'active' : '' ?>">Cancelled <span class="count">(<?= $stats['cancelled'] ?>)</span></a>
        </div>

        <?php if (!empty($appointments)): ?>
            <div class="data-table-wrapper">
                <div class="table-header">
                    <h3><i class="fas fa-list"></i> <?= ucfirst($statusFilter ?: 'All') ?> Appointments (<?= count($appointments) ?>)</h3>
                </div>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Type</th>
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
                                    <td>Dr. <?= htmlspecialchars($apt['doctor_first_name'] . ' ' . $apt['doctor_last_name']) ?></td>
                                    <td><?= ucfirst($apt['type'] ?? 'in-person') ?></td>
                                    <td><span class="apt-status <?= $apt['status'] ?>"><?= ucfirst($apt['status']) ?></span></td>
                                    <td class="actions-cell">
                                        <?php if ($apt['status'] === 'pending'): ?>
                                            <form method="POST" action="/admin/appointments/<?= $apt['id'] ?>/confirm" style="display:inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i> Confirm</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($apt['status'] === 'pending' || $apt['status'] === 'confirmed'): ?>
                                            <form method="POST" action="/admin/appointments/<?= $apt['id'] ?>/cancel" style="display:inline" onsubmit="return confirm('Cancel this appointment?')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-times"></i> Cancel</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state-enhanced">
                <i class="fas fa-calendar-times"></i>
                <h3>No appointments found</h3>
                <p>There are no <?= $statusFilter ? htmlspecialchars($statusFilter) : '' ?> appointments to display.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

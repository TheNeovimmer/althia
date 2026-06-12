<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-chart-pie" style="margin-right:12px;color:var(--primary);"></i>Doctor Dashboard</h1>
                <p>Welcome, Dr. <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></p>
            </div>
            <div class="actions-bar">
                <a href="/doctor/appointments" class="btn btn-outline btn-sm"><i class="fas fa-calendar"></i> All Appointments</a>
            </div>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card-gradient">
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(13,110,253,0.12),rgba(13,110,253,0.04));color:#0d6efd;">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number"><?= $stats['todayAppointments'] ?></span>
                    <span class="stat-label">Today's Appointments</span>
                </div>
            </div>
            <div class="stat-card-gradient">
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(25,135,84,0.12),rgba(25,135,84,0.04));color:#198754;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number"><?= $stats['totalPatients'] ?></span>
                    <span class="stat-label">Patients</span>
                </div>
            </div>
            <div class="stat-card-gradient">
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(255,193,7,0.12),rgba(255,193,7,0.04));color:#ffc107;">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number"><?= $stats['totalAppointments'] ?></span>
                    <span class="stat-label">Total Appointments</span>
                </div>
            </div>
            <div class="stat-card-gradient">
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(111,66,193,0.12),rgba(111,66,193,0.04));color:#6f42c1;">
                    <i class="fas fa-prescription"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number"><?= $stats['totalPrescriptions'] ?></span>
                    <span class="stat-label">Prescriptions</span>
                </div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-card-header">
                    <h3><i class="fas fa-chart-bar"></i> Weekly Appointments</h3>
                </div>
                <div class="chart-card-body">
                    <canvas id="doctorWeekChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-card-header">
                    <h3><i class="fas fa-chart-doughnut"></i> Appointment Status</h3>
                </div>
                <div class="chart-card-body">
                    <canvas id="doctorStatusChart"></canvas>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card dashboard-card-full">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-day"></i> Today's Appointments</h3>
                    <a href="/doctor/appointments" class="btn-link">View All <i class="fas fa-arrow-right" style="font-size:0.75rem;"></i></a>
                </div>
                <div class="card-body">
                    <?php if (!empty($todayAppointments)): ?>
                        <div class="table-wrapper">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Patient</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($todayAppointments as $apt): ?>
                                        <tr>
                                            <td><strong><?= date('H:i', strtotime($apt['appointment_time'])) ?></strong></td>
                                            <td>
                                                <div style="display:flex;align-items:center;gap:8px;">
                                                    <?php if (!empty($apt['avatar'])): ?>
                                                        <img src="<?= asset($apt['avatar']) ?>" alt="" style="width:28px;height:28px;border-radius:50%;object-fit:cover;">
                                                    <?php else: ?>
                                                        <div style="width:28px;height:28px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:700;">
                                                            <?= strtoupper(substr($apt['first_name'] ?? 'P', 0, 1)) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?= htmlspecialchars($apt['first_name'] . ' ' . $apt['last_name']) ?>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars(truncate($apt['reason'] ?? '', 40)) ?></td>
                                            <td><span class="apt-status <?= $apt['status'] ?>"><?= ucfirst($apt['status']) ?></span></td>
                                            <td class="actions-cell">
                                                <form method="POST" action="/doctor/appointments/<?= $apt['id'] ?>/complete" style="display:inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                                                </form>
                                                <form method="POST" action="/doctor/appointments/<?= $apt['id'] ?>/cancel" style="display:inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-outline" style="color:#dc3545;border-color:#dc3545;"><i class="fas fa-times"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" style="padding:40px 24px;">
                            <i class="fas fa-calendar-day" style="font-size:2.5rem;opacity:0.3;"></i>
                            <h3 style="margin-top:12px;">No appointments today</h3>
                            <p class="text-muted">Enjoy your day off!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (isset($chartData)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('doctorWeekChart'), {
        type: 'bar',
        data: {
            labels: <?= $chartData['weekLabels'] ?>,
            datasets: [{
                label: 'Appointments',
                data: <?= $chartData['weekData'] ?>,
                backgroundColor: 'rgba(13,110,253,0.7)',
                borderColor: '#0d6efd',
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, grid: { display: false } }, x: { grid: { display: false } } }
        }
    });

    new Chart(document.getElementById('doctorStatusChart'), {
        type: 'doughnut',
        data: {
            labels: <?= $chartData['statusLabels'] ?>,
            datasets: [{
                data: <?= $chartData['statusData'] ?>,
                backgroundColor: <?= $chartData['statusColors'] ?>,
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            cutout: '70%',
            plugins: { legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true } } }
        }
    });
});
</script>
<?php endif; ?>

<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-chart-pie" style="margin-right:12px;color:var(--primary);"></i>Admin Dashboard</h1>
                <p>System overview and analytics</p>
            </div>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card-gradient">
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(13,110,253,0.12),rgba(13,110,253,0.04));color:#0d6efd;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number"><?= $stats['totalUsers'] ?? 0 ?></span>
                    <span class="stat-label">Total Users</span>
                </div>
            </div>
            <div class="stat-card-gradient">
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(25,135,84,0.12),rgba(25,135,84,0.04));color:#198754;">
                    <i class="fas fa-user-md"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number"><?= $stats['totalDoctors'] ?? 0 ?></span>
                    <span class="stat-label">Doctors</span>
                </div>
            </div>
            <div class="stat-card-gradient">
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(255,193,7,0.12),rgba(255,193,7,0.04));color:#ffc107;">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number"><?= $stats['totalAppointments'] ?? 0 ?></span>
                    <span class="stat-label">Appointments</span>
                </div>
            </div>
            <div class="stat-card-gradient">
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(111,66,193,0.12),rgba(111,66,193,0.04));color:#6f42c1;">
                    <i class="fas fa-notes-medical"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number"><?= $stats['totalRecords'] ?? 0 ?></span>
                    <span class="stat-label">Records</span>
                </div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-card-header">
                    <h3><i class="fas fa-chart-bar"></i> Weekly Appointments</h3>
                </div>
                <div class="chart-card-body">
                    <canvas id="adminAppointmentsChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-card-header">
                    <h3><i class="fas fa-chart-doughnut"></i> User Distribution</h3>
                </div>
                <div class="chart-card-body">
                    <canvas id="adminRolesChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-card-header">
                    <h3><i class="fas fa-chart-pie"></i> Appointment Status</h3>
                </div>
                <div class="chart-card-body">
                    <canvas id="adminStatusChart"></canvas>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-users"></i> Recent Users</h3>
                    <a href="/admin/users" class="btn-link">Manage <i class="fas fa-arrow-right" style="font-size:0.75rem;"></i></a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentUsers)): ?>
                        <?php foreach ($recentUsers as $u): ?>
                            <div class="user-mini">
                                <div class="user-mini-avatar">
                                    <?php if (!empty($u['avatar'])): ?>
                                        <img src="<?= asset($u['avatar']) ?>" alt="" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                                    <?php else: ?>
                                        <span><?= strtoupper(substr($u['first_name'], 0, 1)) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="user-mini-info">
                                    <strong><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></strong>
                                    <span><?= htmlspecialchars($u['email']) ?> — <?= ucfirst($u['role']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No users yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-check"></i> Upcoming Appointments</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($upcomingAppointments)): ?>
                        <?php foreach ($upcomingAppointments as $apt): ?>
                            <div class="appointment-item">
                                <div class="apt-date">
                                    <span class="apt-day"><?= date('d', strtotime($apt['appointment_date'])) ?></span>
                                    <span class="apt-month"><?= date('M', strtotime($apt['appointment_date'])) ?></span>
                                </div>
                                <div class="apt-info">
                                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:2px;">
                                        <?php if (!empty($apt['patient_avatar'])): ?>
                                            <img src="<?= asset($apt['patient_avatar']) ?>" alt="" style="width:22px;height:22px;border-radius:50%;object-fit:cover;">
                                        <?php endif; ?>
                                        <strong><?= htmlspecialchars($apt['patient_first_name'] . ' ' . $apt['patient_last_name']) ?></strong>
                                    </div>
                                    <span>Dr. <?= htmlspecialchars($apt['doctor_first_name'] . ' ' . $apt['doctor_last_name']) ?></span>
                                </div>
                                <span class="apt-status <?= $apt['status'] ?>"><?= ucfirst($apt['status']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No upcoming appointments.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (isset($chartData)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('adminAppointmentsChart'), {
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
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { display: false } },
                x: { grid: { display: false } }
            }
        }
    });

    new Chart(document.getElementById('adminRolesChart'), {
        type: 'doughnut',
        data: {
            labels: <?= $chartData['roleLabels'] ?>,
            datasets: [{
                data: <?= $chartData['roleData'] ?>,
                backgroundColor: <?= $chartData['roleColors'] ?>,
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true } }
            }
        }
    });

    new Chart(document.getElementById('adminStatusChart'), {
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
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { position: 'bottom', labels: { padding: 14, usePointStyle: true } }
            }
        }
    });
});
</script>
<?php endif; ?>

<?php $stats = ['appointments' => count($upcomingAppointments), 'records' => count($records), 'prescriptions' => count($recentPrescriptions)]; ?>
<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-heartbeat" style="margin-right:12px;color:var(--primary);"></i>Welcome, <?= htmlspecialchars($user['first_name'] ?? $patient['first_name']) ?></h1>
                <p>Your health management dashboard</p>
            </div>
            <div class="actions-bar">
                <a href="/patient/appointments/create" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Book Appointment</a>
            </div>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card-gradient">
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(13,110,253,0.12),rgba(13,110,253,0.04));color:#0d6efd;">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number"><?= $stats['appointments'] ?></span>
                    <span class="stat-label">Upcoming</span>
                </div>
            </div>
            <div class="stat-card-gradient">
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(25,135,84,0.12),rgba(25,135,84,0.04));color:#198754;">
                    <i class="fas fa-notes-medical"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number"><?= $stats['records'] ?></span>
                    <span class="stat-label">Records</span>
                </div>
            </div>
            <div class="stat-card-gradient">
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(255,193,7,0.12),rgba(255,193,7,0.04));color:#ffc107;">
                    <i class="fas fa-prescription"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number"><?= $stats['prescriptions'] ?></span>
                    <span class="stat-label">Prescriptions</span>
                </div>
            </div>
            <div class="stat-card-gradient">
                <div class="stat-icon" style="background:linear-gradient(135deg,rgba(111,66,193,0.12),rgba(111,66,193,0.04));color:#6f42c1;">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number">0</span>
                    <span class="stat-label">Alerts</span>
                </div>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-card-header">
                    <h3><i class="fas fa-chart-bar"></i> Appointment History (7 days)</h3>
                </div>
                <div class="chart-card-body">
                    <canvas id="patientHistoryChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-card-header">
                    <h3><i class="fas fa-chart-doughnut"></i> Appointment Status</h3>
                </div>
                <div class="chart-card-body">
                    <canvas id="patientStatusChart"></canvas>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-check"></i> Upcoming Appointments</h3>
                    <a href="/patient/appointments" class="btn-link">View All <i class="fas fa-arrow-right" style="font-size:0.75rem;"></i></a>
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
                                    <strong>Dr. <?= htmlspecialchars($apt['doctor_first_name'] . ' ' . $apt['doctor_last_name']) ?></strong>
                                    <span><?= htmlspecialchars($apt['specialization_name'] ?? 'General') ?> — <?= date('H:i', strtotime($apt['appointment_time'])) ?></span>
                                </div>
                                <span class="apt-status <?= $apt['status'] ?>"><?= ucfirst($apt['status']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No upcoming appointments.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-prescription"></i> Recent Prescriptions</h3>
                    <a href="/patient/prescriptions" class="btn-link">View All <i class="fas fa-arrow-right" style="font-size:0.75rem;"></i></a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentPrescriptions)): ?>
                        <?php foreach ($recentPrescriptions as $rx): ?>
                            <div class="rx-item">
                                <strong><?= htmlspecialchars($rx['medication_name']) ?></strong>
                                <span><?= htmlspecialchars($rx['dosage']) ?> — <?= htmlspecialchars($rx['frequency']) ?></span>
                                <span class="rx-date">Prescribed: <?= date('M j, Y', strtotime($rx['created_at'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No prescriptions yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (isset($chartData)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('patientHistoryChart'), {
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

    new Chart(document.getElementById('patientStatusChart'), {
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

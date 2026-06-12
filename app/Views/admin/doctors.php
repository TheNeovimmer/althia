<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-user-md" style="margin-right:12px;color:var(--primary);"></i>Manage Doctors</h1>
                <p>All registered doctors and their details</p>
            </div>
            <div class="actions-bar">
                <a href="/admin/doctors/create" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Add Doctor</a>
            </div>
        </div>

        <?= flash_message() ?>

        <?php if (!empty($doctors)): ?>
            <div class="data-table-wrapper">
                <div class="table-header">
                    <h3><i class="fas fa-stethoscope"></i> All Doctors (<?= count($doctors) ?>)</h3>
                </div>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Doctor</th>
                                <th>Email</th>
                                <th>Specialization</th>
                                <th>License</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($doctors as $d): ?>
                                <tr>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <?php if (!empty($d['avatar'])): ?>
                                                <img src="<?= asset($d['avatar']) ?>" alt="" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                                            <?php else: ?>
                                                <div style="width:32px;height:32px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;flex-shrink:0;">
                                                    <?= strtoupper(substr($d['first_name'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                            <strong>Dr. <?= htmlspecialchars($d['first_name'] . ' ' . $d['last_name']) ?></strong>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($d['email']) ?></td>
                                    <td><?= htmlspecialchars($d['specialization_name'] ?? 'General') ?></td>
                                    <td><?= htmlspecialchars($d['license_number'] ?? 'N/A') ?></td>
                                    <td><span class="apt-status <?= $d['is_active'] ? 'scheduled' : 'cancelled' ?>"><?= $d['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                                    <td class="actions-cell">
                                        <a href="/admin/doctors/<?= $d['id'] ?>/edit" class="btn btn-sm btn-outline"><i class="fas fa-edit"></i> Edit</a>
                                        <form method="POST" action="/admin/doctors/<?= $d['id'] ?>/toggle-status" style="display:inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm <?= $d['is_active'] ? 'btn-outline' : 'btn-primary' ?>">
                                                <?= $d['is_active'] ? 'Deactivate' : 'Activate' ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state-enhanced">
                <i class="fas fa-user-md"></i>
                <h3>No doctors found</h3>
                <p>Click "Add Doctor" to register the first doctor.</p>
                <a href="/admin/doctors/create" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Add Doctor</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-users-gear" style="margin-right:12px;color:var(--primary);"></i>Manage Users</h1>
                <p>All registered users across the platform</p>
            </div>
        </div>

        <?= flash_message() ?>

        <?php if (!empty($users)): ?>
            <div class="data-table-wrapper">
                <div class="table-header">
                    <h3><i class="fas fa-list"></i> All Users (<?= count($users) ?>)</h3>
                </div>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <?php if (!empty($u['avatar'])): ?>
                                                <img src="<?= asset($u['avatar']) ?>" alt="" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                                            <?php else: ?>
                                                <div style="width:32px;height:32px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;flex-shrink:0;">
                                                    <?= strtoupper(substr($u['first_name'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                            <strong><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></strong>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><span class="role-badge role-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                                    <td><span class="apt-status <?= $u['is_active'] ? 'scheduled' : 'cancelled' ?>"><?= $u['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                                    <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                                    <td class="actions-cell">
                                        <form method="POST" action="/admin/users/<?= $u['id'] ?>/toggle-status" style="display:inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm <?= $u['is_active'] ? 'btn-outline' : 'btn-primary' ?>">
                                                <?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>
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
                <i class="fas fa-users"></i>
                <h3>No users found</h3>
                <p>Users will appear here once they register.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

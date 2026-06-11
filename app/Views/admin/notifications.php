<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-bell" style="margin-right:12px;color:var(--primary);"></i>Notifications</h1>
                <p>System notifications and updates</p>
            </div>
            <?php if (!empty($notifs)): ?>
                <form method="POST" action="/admin/notifications/mark-all-read">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-check-double"></i> Mark All Read</button>
                </form>
            <?php endif; ?>
        </div>

        <?= flash_message() ?>

        <?php if (!empty($notifs)): ?>
            <div class="notifications-list">
                <?php foreach ($notifs as $n): ?>
                    <div class="notification-item <?= !$n['is_read'] ? 'unread' : '' ?>">
                        <div class="notif-icon" style="background:<?= $n['type'] === 'appointment' ? 'rgba(13,110,253,0.12)' : ($n['type'] === 'message' ? 'rgba(25,135,84,0.12)' : 'rgba(255,193,7,0.12)') ?>;color:<?= $n['type'] === 'appointment' ? '#0d6efd' : ($n['type'] === 'message' ? '#198754' : '#ffc107') ?>;">
                            <i class="fas fa-<?= $n['type'] === 'appointment' ? 'calendar-check' : ($n['type'] === 'message' ? 'envelope' : 'bell') ?>"></i>
                        </div>
                        <div class="notif-content">
                            <strong><?= htmlspecialchars($n['title']) ?></strong>
                            <p><?= htmlspecialchars($n['message']) ?></p>
                        </div>
                        <span class="notif-time"><?= timeAgo($n['created_at']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state-enhanced">
                <i class="fas fa-bell-slash"></i>
                <h3>No notifications</h3>
                <p>You're all caught up!</p>
            </div>
        <?php endif; ?>
    </div>
</section>

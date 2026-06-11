<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-envelope" style="margin-right:12px;color:var(--primary);"></i>Contact Submissions</h1>
                <p>Messages from the contact form (<?= $unreadCount ?> unread)</p>
            </div>
        </div>

        <?= flash_message() ?>

        <?php if (!empty($contacts)): ?>
            <div class="data-table-wrapper">
                <div class="table-header">
                    <h3><i class="fas fa-list"></i> All Messages</h3>
                </div>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contacts as $c): ?>
                                <tr style="<?= !$c['is_read'] ? 'font-weight:600;' : '' ?>">
                                    <td><?= htmlspecialchars(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars($c['email'] ?? '') ?></td>
                                    <td><?= htmlspecialchars(truncate($c['subject'] ?? 'No subject', 30)) ?></td>
                                    <td><?= htmlspecialchars(truncate($c['message'] ?? '', 50)) ?></td>
                                    <td><?= date('M j, Y', strtotime($c['created_at'])) ?></td>
                                    <td class="actions-cell">
                                        <?php if (!$c['is_read']): ?>
                                            <form method="POST" action="/admin/contacts/<?= $c['id'] ?>/mark-read" style="display:inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline"><i class="fas fa-check"></i> Mark Read</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" action="/admin/contacts/<?= $c['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this message?')">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
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
                <i class="fas fa-envelope-open"></i>
                <h3>No messages</h3>
                <p>Contact form submissions will appear here.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

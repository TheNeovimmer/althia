<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-comments" style="margin-right:12px;color:var(--primary);"></i>Messages</h1>
                <p>Communicate with your doctors</p>
            </div>
            <button class="btn btn-primary" onclick="document.getElementById('newMessageModal').style.display='flex'"><i class="fas fa-plus-circle"></i> New Message</button>
        </div>

        <?= flash_message() ?>

        <?php if (!empty($conversations)): ?>
            <div class="conversation-list">
                <?php foreach ($conversations as $conv): ?>
                    <a href="/patient/messages/conversation/<?= $conv['partner_id'] ?? $conv['sender_id'] ?>" class="conversation-item <?= ($conv['unread_count'] ?? 0) > 0 ? 'unread' : '' ?>">
                        <div class="conv-avatar" style="background:var(--primary);color:#fff;width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                            <?= strtoupper(substr($conv['first_name'], 0, 1)) ?>
                        </div>
                        <div class="conv-content">
                            <strong>Dr. <?= htmlspecialchars($conv['first_name'] . ' ' . $conv['last_name']) ?></strong>
                            <p><?= htmlspecialchars(truncate($conv['body'] ?? 'No message', 60)) ?></p>
                        </div>
                        <div class="conv-meta">
                            <span class="conv-time"><?= timeAgo($conv['created_at']) ?></span>
                            <?php if (($conv['unread_count'] ?? 0) > 0): ?>
                                <span class="badge-unread"><?= $conv['unread_count'] ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state-enhanced">
                <i class="fas fa-comments"></i>
                <h3>No conversations yet</h3>
                <p>Start a conversation with your doctor.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<div id="newMessageModal" class="modal-overlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-content form-card" style="max-width:500px;margin:auto;padding:24px;border-radius:16px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h3 style="margin:0;"><i class="fas fa-paper-plane" style="color:var(--primary);margin-right:8px;"></i>New Message</h3>
            <button onclick="document.getElementById('newMessageModal').style.display='none'" style="background:none;border:none;font-size:1.4rem;cursor:pointer;">&times;</button>
        </div>
        <form method="POST" action="/patient/messages/send">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="receiver_id">Doctor</label>
                <select id="receiver_id" name="receiver_id" required>
                    <option value="">Select a doctor...</option>
                    <?php foreach ($doctors as $doc): ?>
                        <option value="<?= $doc['user_id'] ?>">Dr. <?= htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" placeholder="Subject">
            </div>
            <div class="form-group">
                <label for="body">Message</label>
                <textarea id="body" name="body" rows="5" placeholder="Type your message..." required></textarea>
            </div>
            <div class="form-actions" style="padding-top:12px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('newMessageModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send</button>
            </div>
        </form>
    </div>
</div>

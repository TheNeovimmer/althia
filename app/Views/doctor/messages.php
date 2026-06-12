<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-comments"></i> Messages</h1>
                <p>Communicate with your patients</p>
            </div>
            <button class="btn btn-primary" onclick="openNewMessage()">
                <i class="fas fa-plus-circle"></i> New Message
            </button>
        </div>

        <?= flash_message() ?>

        <div class="msg-inbox">
            <?php if (!empty($conversations)): ?>
                <?php foreach ($conversations as $conv): ?>
                    <?php
                        $partnerId = $conv['partner_id'] ?? $conv['sender_id'];
                        $unread = $conv['unread_count'] ?? 0;
                        $name = htmlspecialchars($conv['first_name'] . ' ' . $conv['last_name']);
                        $preview = htmlspecialchars(truncate($conv['body'] ?? 'No messages yet', 70));
                        $time = timeAgo($conv['created_at']);
                        $initial = strtoupper(substr($conv['first_name'], 0, 1));
                    ?>
                    <a href="/doctor/messages/conversation/<?= $partnerId ?>" class="msg-row <?= $unread > 0 ? 'msg-unread' : '' ?>">
                        <div class="msg-avatar"><?= $initial ?></div>
                        <div class="msg-body">
                            <div class="msg-top">
                                <span class="msg-name"><?= $name ?></span>
                                <span class="msg-time"><?= $time ?></span>
                            </div>
                            <div class="msg-preview"><?= $preview ?></div>
                        </div>
                        <?php if ($unread > 0): ?>
                            <span class="msg-badge"><?= $unread ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state-enhanced">
                    <i class="fas fa-comments"></i>
                    <h3>No conversations yet</h3>
                    <p>Start a conversation with one of your patients.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="modal-overlay" id="newMessageOverlay">
    <div class="modal-panel">
        <div class="modal-head">
            <h3><i class="fas fa-paper-plane"></i> New Message</h3>
            <button class="modal-close" onclick="closeNewMessage()">&times;</button>
        </div>
        <form method="POST" action="/doctor/messages/send">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label" for="receiver_id"><i class="fas fa-user"></i> Patient</label>
                <select id="receiver_id" name="receiver_id" class="form-select" required>
                    <option value="">Select a patient…</option>
                    <?php foreach ($patients as $p): ?>
                        <option value="<?= $p['user_id'] ?>"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="subject"><i class="fas fa-heading"></i> Subject</label>
                <input type="text" id="subject" name="subject" class="form-input" placeholder="What is this about?">
            </div>
            <div class="form-group">
                <label class="form-label" for="body"><i class="fas fa-edit"></i> Message</label>
                <textarea id="body" name="body" class="form-textarea" rows="5" placeholder="Type your message…" required></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-outline" onclick="closeNewMessage()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send</button>
            </div>
        </form>
    </div>
</div>

<script>
function openNewMessage() {
    document.getElementById('newMessageOverlay').classList.add('active');
}
function closeNewMessage() {
    document.getElementById('newMessageOverlay').classList.remove('active');
}
document.getElementById('newMessageOverlay')?.addEventListener('click', function(e) {
    if (e.target === this) closeNewMessage();
});
</script>

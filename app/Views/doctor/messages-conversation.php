<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header" style="border-bottom:none;margin-bottom:0;">
            <div>
                <h1><i class="fas fa-comment-dots" style="margin-right:12px;color:var(--primary);"></i>
                    Conversation with <?= htmlspecialchars($patientUser['first_name'] . ' ' . $patientUser['last_name']) ?>
                </h1>
                <p><?= htmlspecialchars($patientUser['email'] ?? '') ?></p>
            </div>
            <a href="/doctor/messages" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <?= flash_message() ?>

        <div class="conversation-container" style="display:flex;flex-direction:column;gap:16px;max-height:500px;overflow-y:auto;padding:16px;background:var(--bg-darker);border-radius:12px;margin-bottom:20px;">
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message-bubble <?= $msg['sender_id'] == Auth::id() ? 'sent' : 'received' ?>" style="max-width:75%;padding:12px 16px;border-radius:12px;align-self:<?= $msg['sender_id'] == Auth::id() ? 'flex-end' : 'flex-start' ?>;background:<?= $msg['sender_id'] == Auth::id() ? 'var(--primary)' : 'var(--bg-card)' ?>;color:<?= $msg['sender_id'] == Auth::id() ? '#fff' : 'var(--text)' ?>;">
                        <?php if ($msg['subject']): ?>
                            <strong style="display:block;margin-bottom:4px;font-size:0.85rem;"><?= htmlspecialchars($msg['subject']) ?></strong>
                        <?php endif; ?>
                        <p style="margin:0;white-space:pre-wrap;"><?= htmlspecialchars($msg['body']) ?></p>
                        <small style="display:block;margin-top:6px;opacity:0.7;font-size:0.75rem;"><?= date('M j, H:i', strtotime($msg['created_at'])) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state-enhanced" style="background:transparent;">
                    <i class="fas fa-comment-slash"></i>
                    <h3>No messages yet</h3>
                    <p>Start the conversation below.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="form-card">
            <form method="POST" action="/doctor/messages/send">
                <?= csrf_field() ?>
                <input type="hidden" name="receiver_id" value="<?= $patientUserId ?>">
                <div class="form-group">
                    <input type="text" name="subject" placeholder="Subject (optional)" style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:8px;">
                </div>
                <div class="form-row" style="gap:8px;">
                    <textarea name="body" rows="3" placeholder="Type your message..." required style="flex:1;padding:10px 14px;border:1px solid var(--border);border-radius:8px;resize:vertical;"></textarea>
                    <button type="submit" class="btn btn-primary" style="align-self:flex-end;padding:10px 20px;"><i class="fas fa-paper-plane"></i> Send</button>
                </div>
            </form>
        </div>
    </div>
</section>

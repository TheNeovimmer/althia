<section class="dashboard-section">
    <div class="container">
        <div class="conv-head">
            <a href="/doctor/messages" class="conv-back"><i class="fas fa-arrow-left"></i></a>
            <div class="conv-head-avatar"><?= strtoupper(substr($patientUser['first_name'] ?? 'U', 0, 1)) ?></div>
            <div class="conv-head-info">
                <h2><?= htmlspecialchars($patientUser['first_name'] . ' ' . $patientUser['last_name']) ?></h2>
                <span><?= htmlspecialchars($patientUser['email'] ?? '') ?></span>
            </div>
        </div>

        <?= flash_message() ?>

        <div class="conv-thread" id="convThread">
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $msg): ?>
                    <?php $isMine = $msg['sender_id'] == \App\Core\Auth::id(); ?>
                    <div class="conv-bubble <?= $isMine ? 'bubble-mine' : 'bubble-theirs' ?>">
                        <?php if ($msg['subject']): ?>
                            <div class="bubble-subject"><?= htmlspecialchars($msg['subject']) ?></div>
                        <?php endif; ?>
                        <div class="bubble-text"><?= htmlspecialchars($msg['body']) ?></div>
                        <div class="bubble-time"><?= date('M j, H:i', strtotime($msg['created_at'])) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state-enhanced">
                    <i class="fas fa-comment-slash"></i>
                    <h3>No messages yet</h3>
                    <p>Send the first message to start the conversation.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="conv-reply">
            <form method="POST" action="/doctor/messages/send">
                <?= csrf_field() ?>
                <input type="hidden" name="receiver_id" value="<?= $patientUserId ?>">
                <div class="reply-row">
                    <input type="text" name="subject" class="reply-subject" placeholder="Subject (optional)">
                </div>
                <div class="reply-row">
                    <textarea name="body" rows="2" class="reply-input" placeholder="Type your message…" required></textarea>
                    <button type="submit" class="btn btn-primary reply-send"><i class="fas fa-paper-plane"></i></button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
const thread = document.getElementById('convThread');
if (thread) thread.scrollTop = thread.scrollHeight;
</script>

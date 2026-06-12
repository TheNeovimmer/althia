<div class="auth-section">
    <div class="auth-card">
        <div class="auth-header">
                    <div class="auth-logo">
                        <img src="<?= asset('images/logo.png') ?>" alt="Althia" height="36">
                    </div>
            <h2>Forgot Password</h2>
            <p>Enter your email and we'll send you a reset link</p>
        </div>

        <?php if (hasError('email')): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars(error('email')) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/forgot-password" class="auth-form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-input" value="<?= htmlspecialchars(old('email')) ?>" placeholder="you@email.com" required>
            </div>
            <button type="submit" class="btn btn-primary w-full">Send Reset Link</button>
        </form>

        <div class="auth-footer">
            <p><a href="/login"><i class="fas fa-arrow-left"></i> Back to login</a></p>
        </div>
    </div>
</div>

<div class="auth-section">
    <div class="auth-card">
        <div class="auth-header">
                    <div class="auth-logo">
                        <img src="<?= asset('images/logo.png') ?>" alt="Althia" height="36">
                    </div>
            <h2>Welcome Back</h2>
            <p>Sign in to your account to continue</p>
        </div>

        <?php if (hasError('email') || hasError('password')): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars(error('email') ?? error('password')) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/login" class="auth-form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-input" value="<?= htmlspecialchars(old('email')) ?>" placeholder="you@email.com" required>
            </div>
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <div class="form-group form-check">
                <label>
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <a href="/forgot-password" class="forgot-link">Forgot password?</a>
            </div>
            <button type="submit" class="btn btn-primary w-full">Sign In</button>
        </form>

        <div class="auth-footer">
            <p>Don't have an account? <a href="/register">Register here</a></p>
        </div>
    </div>
</div>

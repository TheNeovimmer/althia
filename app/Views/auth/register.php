<div class="auth-section">
    <div class="container">
        <div class="auth-card auth-card-lg">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-plus-circle"></i>
                    <span>Medicase</span>
                </div>
                <h2>Create Account</h2>
                <p>Join Medicase and take control of your healthcare</p>
            </div>

            <?php
            $errors = $_SESSION['_errors'] ?? [];
            unset($_SESSION['_errors']);
            ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="/register" class="auth-form">
                <?= csrf_field() ?>
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-input" value="<?= htmlspecialchars(old('first_name')) ?>" placeholder="Your first name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-input" value="<?= htmlspecialchars(old('last_name')) ?>" placeholder="Your last name" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" value="<?= htmlspecialchars(old('email')) ?>" placeholder="you@email.com" required>
                </div>
                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-input" value="<?= htmlspecialchars(old('phone')) ?>" placeholder="+216" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" id="password" name="password" class="form-input" placeholder="Min. 8 characters" required>
                            <button type="button" class="toggle-password" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" placeholder="Confirm your password" required>
                            <button type="button" class="toggle-password" onclick="togglePassword('password_confirmation')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">I am a</label>
                    <div class="form-radio-group">
                        <label class="radio-label">
                            <input type="radio" name="role" value="patient" checked>
                            <span>Patient</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="role" value="doctor">
                            <span>Doctor</span>
                        </label>
                    </div>
                </div>
                <div class="form-group form-check">
                    <label>
                        <input type="checkbox" name="terms" required>
                        I agree to the <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a>
                    </label>
                </div>
                <button type="submit" class="btn btn-primary w-full">Create Account</button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="/login">Sign in</a></p>
            </div>
        </div>
    </div>
</div>

<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-user-circle" style="color:var(--primary);margin-right:12px;"></i>My Profile</h1>
                <p>Manage your personal information</p>
            </div>
        </div>

        <?= flash_message() ?>

        <div class="profile-layout">
            <div class="profile-card">
                <div class="profile-avatar">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="<?= asset($user['avatar']) ?>" alt="Avatar">
                    <?php else: ?>
                        <div class="avatar-placeholder"><?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1) . substr($user['last_name'] ?? 'U', 0, 1)) ?></div>
                    <?php endif; ?>
                </div>
                <h3><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h3>
                <span class="profile-email"><?= htmlspecialchars($user['email']) ?></span>
                <span class="profile-role">Patient</span>
            </div>

            <div>
                <div class="profile-form-card">
                    <div class="form-section-title">Personal Information</div>
                    <form method="POST" action="/patient/profile" enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <div class="form-group">
                            <label for="avatar" class="form-label"><i class="fas fa-camera"></i> Profile Photo</label>
                            <div class="file-input-wrapper">
                                <div class="file-input-icon"><i class="fas fa-cloud-arrow-up"></i></div>
                                <div class="file-input-text">Drop your photo here or <span>browse</span></div>
                                <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/gif">
                                <span class="file-input-hint">JPG, PNG or GIF. Max 10MB.</span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name" class="form-label"><i class="fas fa-user"></i> First Name</label>
                                <input type="text" id="first_name" name="first_name" class="form-input" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name" class="form-label"><i class="fas fa-user"></i> Last Name</label>
                                <input type="text" id="last_name" name="last_name" class="form-input" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email" class="form-label"><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" id="email" name="email" class="form-input" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="phone" class="form-label"><i class="fas fa-phone"></i> Phone</label>
                                <input type="tel" id="phone" name="phone" class="form-input" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+1 (555) 123-4567">
                            </div>
                        </div>

                        <div style="margin-top:24px;">
                            <div class="form-section-title">Medical Information</div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="date_of_birth" class="form-label"><i class="fas fa-cake-candles"></i> Date of Birth</label>
                                <input type="date" id="date_of_birth" name="date_of_birth" class="form-input" value="<?= htmlspecialchars($patient['date_of_birth'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="blood_type" class="form-label"><i class="fas fa-droplet"></i> Blood Type</label>
                                <select id="blood_type" name="blood_type" class="form-select">
                                    <option value="">Select</option>
                                    <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bt): ?>
                                        <option value="<?= $bt ?>" <?= ($patient['blood_type'] ?? '') === $bt ? 'selected' : '' ?>><?= $bt ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address" class="form-label"><i class="fas fa-location-dot"></i> Address</label>
                            <textarea id="address" name="address" class="form-textarea" rows="3" placeholder="Your address"><?= htmlspecialchars($patient['address'] ?? '') ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Profile</button>
                        </div>
                    </form>
                </div>

                <div class="profile-section">
                    <div class="profile-form-card">
                        <div class="form-section-title">Change Password</div>
                        <form method="POST" action="/patient/profile">
                            <?= csrf_field() ?>
                            <p class="form-section-desc">Leave blank to keep your current password.</p>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="new_password" class="form-label"><i class="fas fa-lock"></i> New Password</label>
                                    <input type="password" id="new_password" name="new_password" class="form-input" minlength="8" placeholder="Leave blank to keep current">
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password" class="form-label"><i class="fas fa-lock"></i> Confirm Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" minlength="8" placeholder="Confirm new password">
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Change Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

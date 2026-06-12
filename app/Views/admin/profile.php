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
                <span class="profile-role">Administrator</span>
            </div>

            <div>
                <div class="profile-form-card">
                    <div class="form-section-title">Personal Information</div>
                    <form method="POST" action="/admin/profile" enctype="multipart/form-data">
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

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Profile</button>
                        </div>
                    </form>
                </div>

                <div class="profile-section">
                    <div class="profile-form-card">
                        <div class="form-section-title">Change Password</div>
                        <form method="POST" action="/admin/profile">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_action" value="change_password">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="current_password" class="form-label"><i class="fas fa-lock"></i> Current Password</label>
                                    <input type="password" id="current_password" name="current_password" class="form-input" required placeholder="Enter current password">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="new_password" class="form-label"><i class="fas fa-lock"></i> New Password</label>
                                    <input type="password" id="new_password" name="new_password" class="form-input" minlength="8" required placeholder="New password">
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password" class="form-label"><i class="fas fa-lock"></i> Confirm Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" minlength="8" required placeholder="Confirm new password">
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

<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-user-edit" style="color:var(--primary);margin-right:12px;"></i>Edit Doctor</h1>
                <p>Update doctor information</p>
            </div>
            <a href="/admin/doctors" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <?= flash_message() ?>

        <div class="form-card form-card-lg">
            <form method="POST" action="/admin/doctors/<?= $doctor['id'] ?>/edit" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="form-section">
                    <div class="form-section-title">Personal Information</div>

                    <div class="form-group">
                        <label for="avatar" class="form-label"><i class="fas fa-camera"></i> Profile Photo</label>
                        <div class="file-input-wrapper">
                            <div class="file-input-icon"><i class="fas fa-cloud-arrow-up"></i></div>
                            <div class="file-input-text">Drop photo here or <span>browse</span></div>
                            <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/gif">
                            <span class="file-input-hint">JPG, PNG or GIF. Max 10MB.</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name" class="form-label"><i class="fas fa-user"></i> First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-input" value="<?= htmlspecialchars($doctor['first_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name" class="form-label"><i class="fas fa-user"></i> Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-input" value="<?= htmlspecialchars($doctor['last_name']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" id="email" name="email" class="form-input" value="<?= htmlspecialchars($doctor['email']) ?>" required>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Professional Details</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="specialization_id" class="form-label"><i class="fas fa-stethoscope"></i> Specialization</label>
                            <select id="specialization_id" name="specialization_id" class="form-select" required>
                                <option value="">Select specialization</option>
                                <?php foreach ($specializations as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= ($doctor['specialization_id'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="license_number" class="form-label"><i class="fas fa-id-card"></i> License Number</label>
                            <input type="text" id="license_number" name="license_number" class="form-input" value="<?= htmlspecialchars($doctor['license_number'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="bio" class="form-label"><i class="fas fa-align-left"></i> Bio</label>
                        <textarea id="bio" name="bio" class="form-textarea" rows="4" placeholder="Professional bio..."><?= htmlspecialchars($doctor['bio'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="/admin/doctors" class="btn btn-outline"><i class="fas fa-times"></i> Cancel</a>
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Update Doctor</button>
                </div>
            </form>
        </div>
    </div>
</section>

<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-notes-medical" style="color:var(--primary);margin-right:12px;"></i>Medical Records</h1>
                <p>View your medical history and upload reports</p>
            </div>
        </div>

        <?= flash_message() ?>

        <div class="form-card" style="margin-bottom:28px;">
            <div class="form-section">
                <div class="form-section-title">Upload Medical Report</div>
                <p class="form-section-desc">Share your medical documents securely with your care team.</p>

                <form method="POST" action="/patient/reports/upload" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="title" class="form-label"><i class="fas fa-heading"></i> Report Title</label>
                            <input type="text" id="title" name="title" class="form-input" placeholder="e.g. Blood Test Results" required>
                        </div>
                        <div class="form-group">
                            <label for="type" class="form-label"><i class="fas fa-tag"></i> Type</label>
                            <select id="type" name="type" class="form-select">
                                <option value="lab">Lab</option>
                                <option value="imaging">Imaging</option>
                                <option value="pathology">Pathology</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="report" class="form-label"><i class="fas fa-file"></i> File</label>
                        <div class="file-input-wrapper">
                            <div class="file-input-icon"><i class="fas fa-cloud-arrow-up"></i></div>
                            <div class="file-input-text">Drop your file here or <span>browse</span></div>
                            <input type="file" id="report" name="report" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                            <span class="file-input-hint">PDF, JPG, PNG or DOC. Max 10MB.</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes" class="form-label"><i class="fas fa-sticky-note"></i> Notes</label>
                        <textarea id="notes" name="notes" class="form-textarea" rows="3" placeholder="Optional notes about this report"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload Report</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (!empty($records)): ?>
            <div class="data-table-wrapper">
                <div class="table-header">
                    <h3><i class="fas fa-history"></i> Record History</h3>
                </div>
                <div class="records-list" style="padding:16px;">
                    <?php foreach ($records as $record): ?>
                        <div class="record-card" style="display:flex;gap:16px;padding:16px;border-bottom:1px solid var(--border-light);">
                            <div style="width:44px;height:44px;border-radius:12px;background:rgba(13,110,253,0.08);display:flex;align-items:center;justify-content:center;color:var(--primary);flex-shrink:0;">
                                <i class="fas fa-file-medical"></i>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div style="display:flex;align-items:center;gap:12px;margin-bottom:4px;">
                                    <strong><?= htmlspecialchars($record['diagnosis'] ?? 'Record') ?></strong>
                                    <span style="font-size:0.75rem;color:var(--text-muted);"><?= date('M j, Y', strtotime($record['record_date'])) ?></span>
                                </div>
                                <?php if (!empty($record['doctor_first_name'])): ?>
                                    <p style="font-size:0.84rem;color:var(--text-muted);margin:0 0 4px;">
                                        <i class="fas fa-user-md" style="font-size:0.75rem;"></i>
                                        Dr. <?= htmlspecialchars($record['doctor_first_name'] . ' ' . $record['doctor_last_name']) ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($record['symptoms'])): ?>
                                    <p style="font-size:0.84rem;color:var(--text-muted);margin:0;"><strong>Symptoms:</strong> <?= htmlspecialchars($record['symptoms']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state-enhanced">
                <i class="fas fa-notes-medical"></i>
                <h3>No medical records yet</h3>
                <p>Your medical records will appear here after your first visit.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

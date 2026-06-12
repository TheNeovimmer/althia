<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-stethoscope" style="color:var(--primary);margin-right:12px;"></i>Specializations</h1>
                <p>Manage medical specialties</p>
            </div>
        </div>

        <?= flash_message() ?>

        <div class="profile-layout" style="gap:32px;grid-template-columns:1fr 2fr;">
            <div class="profile-form-card">
                <div class="form-section-title">Add New</div>
                <form method="POST" action="/admin/specializations">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_action" value="create">
                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-input" required placeholder="e.g. Cardiology">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-textarea" rows="3" placeholder="Brief description"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add</button>
                    </div>
                </form>
            </div>

            <div class="data-table-wrapper">
                <div class="table-header">
                    <h3>All Specializations (<?= count($specializations) ?>)</h3>
                </div>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($specializations as $spec): ?>
                            <tr>
                                <td>
                                    <form method="POST" action="/admin/specializations" style="display:flex;gap:8px;align-items:center;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_action" value="update">
                                        <input type="hidden" name="id" value="<?= $spec['id'] ?>">
                                        <input type="text" name="name" value="<?= htmlspecialchars($spec['name']) ?>" class="form-input" style="width:160px;padding:6px 10px;font-size:0.85rem;" required>
                                        <button type="submit" class="btn btn-sm btn-primary" title="Rename"><i class="fas fa-check"></i></button>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" action="/admin/specializations" style="display:flex;gap:8px;align-items:center;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_action" value="update">
                                        <input type="hidden" name="id" value="<?= $spec['id'] ?>">
                                        <input type="hidden" name="name" value="<?= htmlspecialchars($spec['name']) ?>">
                                        <input type="text" name="description" value="<?= htmlspecialchars($spec['description'] ?? '') ?>" class="form-input" style="width:200px;padding:6px 10px;font-size:0.85rem;" placeholder="Description">
                                        <button type="submit" class="btn btn-sm btn-outline" title="Update"><i class="fas fa-check"></i></button>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" action="/admin/specializations" style="display:inline" onsubmit="return confirm('Delete this specialization?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_action" value="delete">
                                        <input type="hidden" name="id" value="<?= $spec['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

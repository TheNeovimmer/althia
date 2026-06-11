<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-tags" style="color:var(--primary);margin-right:12px;"></i>Blog Categories</h1>
                <p>Organize your blog posts by category</p>
            </div>
            <a href="/admin/blog" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Back to Posts</a>
        </div>

        <?= flash_message() ?>

        <div class="dashboard-grid" style="grid-template-columns:1fr 1fr;gap:24px;">
            <div class="form-card" style="margin:0;">
                <div class="form-section">
                    <div class="form-section-title">Add Category</div>
                    <form method="POST" action="/admin/blog/categories">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label for="name" class="form-label"><i class="fas fa-tag"></i> Category Name</label>
                            <input type="text" id="name" name="name" class="form-input" placeholder="e.g. Health Tips" required>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Category</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="form-card" style="margin:0;">
                <div class="form-section" style="border-bottom:none;padding-bottom:0;">
                    <div class="form-section-title">Current Categories</div>
                    <?php if (!empty($categories)): ?>
                        <ul style="list-style:none;padding:0;">
                            <?php foreach ($categories as $cat): ?>
                                <li style="display:flex;justify-content:space-between;align-items:center;padding:14px 0;border-bottom:1px solid var(--border-light);">
                                    <span style="display:flex;align-items:center;gap:10px;">
                                        <span style="width:8px;height:8px;border-radius:50%;background:var(--primary);display:inline-block;"></span>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </span>
                                    <span style="font-size:0.78rem;color:var(--text-muted);font-family:monospace;"><?= htmlspecialchars($cat['slug']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">No categories yet. Create your first one!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

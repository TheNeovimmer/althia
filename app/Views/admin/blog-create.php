<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-pen-to-square" style="color:var(--primary);margin-right:12px;"></i>New Blog Post</h1>
                <p>Create engaging content for your readers</p>
            </div>
            <a href="/admin/blog" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <?= flash_message() ?>

        <div class="form-card form-card-lg">
            <form method="POST" action="/admin/blog/create">
                <?= csrf_field() ?>

                <div class="form-section">
                    <div class="form-section-title">Post Details</div>

                    <div class="form-group">
                        <label for="title" class="form-label"><i class="fas fa-heading"></i> Title</label>
                        <input type="text" id="title" name="title" class="form-input" placeholder="Enter a compelling post title" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="category_id" class="form-label"><i class="fas fa-tag"></i> Category</label>
                            <select id="category_id" name="category_id" class="form-select">
                                <option value="">Uncategorized</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-globe"></i> Status</label>
                            <label class="toggle-wrapper">
                                <input type="checkbox" name="is_published" value="1" checked>
                                <span class="toggle-track"></span>
                                <span class="toggle-label">Publish immediately</span>
                                <span class="toggle-desc">Draft</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">Content</div>

                    <div class="form-group">
                        <label for="excerpt" class="form-label"><i class="fas fa-align-left"></i> Excerpt</label>
                        <textarea id="excerpt" name="excerpt" class="form-textarea" rows="3" placeholder="Brief summary for the blog listing card"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="content" class="form-label"><i class="fas fa-file-lines"></i> Full Content</label>
                        <textarea id="content" name="content" class="form-textarea" rows="16" placeholder="Write your blog post content here..." style="min-height:320px;"></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="/admin/blog" class="btn btn-outline"><i class="fas fa-times"></i> Cancel</a>
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-paper-plane"></i> Create Post</button>
                </div>
            </form>
        </div>
    </div>
</section>

<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-blog" style="margin-right:12px;color:var(--primary);"></i>Blog Management</h1>
                <p>Create and manage blog posts</p>
            </div>
            <div class="actions-bar">
                <a href="/admin/blog/create" class="btn btn-primary"><i class="fas fa-plus-circle"></i> New Post</a>
                <a href="/admin/blog/categories" class="btn btn-outline"><i class="fas fa-tags"></i> Categories</a>
            </div>
        </div>

        <?= flash_message() ?>

        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <div class="blog-card" style="margin-bottom:12px;">
                    <div class="blog-content">
                        <h3><?= htmlspecialchars($post['title']) ?></h3>
                        <div class="blog-meta">
                            <span>By <?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?></span>
                            <span><?= htmlspecialchars($post['category_name'] ?? 'Uncategorized') ?></span>
                            <span><?= $post['is_published'] ? 'Published' : 'Draft' ?></span>
                            <span><?= date('M j, Y', strtotime($post['created_at'])) ?></span>
                        </div>
                        <p><?= htmlspecialchars(truncate($post['excerpt'] ?? '', 200)) ?></p>
                        <div style="margin-top:12px;display:flex;gap:8px;">
                            <a href="/admin/blog/<?= $post['id'] ?>/edit" class="btn btn-sm btn-outline"><i class="fas fa-edit"></i> Edit</a>
                            <form method="POST" action="/admin/blog/<?= $post['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this post?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state-enhanced">
                <i class="fas fa-blog"></i>
                <h3>No blog posts yet</h3>
                <p>Create your first blog post to share health tips and updates.</p>
                <a href="/admin/blog/create" class="btn btn-primary"><i class="fas fa-plus-circle"></i> New Post</a>
            </div>
        <?php endif; ?>
    </div>
</section>

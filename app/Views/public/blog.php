<section class="page-hero">
    <div class="container">
        <h1>Our Blog</h1>
        <p>Latest insights and wellness tips from our experts</p>
    </div>
</section>

<section class="blog-section">
    <div class="container">
        <?php if (!empty($posts)): ?>
            <div class="blog-grid">
                <?php foreach ($posts as $post): ?>
                    <article class="blog-card">
                        <div class="blog-card-image">
                            <img src="<?= htmlspecialchars($post['featured_image'] ?? 'https://static.codia.ai/image/2026-06-11/kGika3hYkv.png') ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                        </div>
                        <div class="blog-card-body">
                            <div class="blog-card-category">
                                <span><?= htmlspecialchars($post['category_name'] ?? 'General') ?></span>
                            </div>
                            <h3><?= htmlspecialchars($post['title']) ?></h3>
                            <p><?= htmlspecialchars(truncate($post['excerpt'] ?? '', 120)) ?></p>
                            <div class="blog-card-meta">
                                <span><i class="fas fa-user"></i> <?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?></span>
                                <span><i class="fas fa-clock"></i> <?= timeAgo($post['published_at'] ?? $post['created_at']) ?></span>
                            </div>
                            <a href="/blog/<?= htmlspecialchars($post['slug']) ?>" class="blog-read-more">
                                READ MORE <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-newspaper"></i>
                <h3>No posts yet</h3>
                <p>Check back soon for the latest healthcare insights.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

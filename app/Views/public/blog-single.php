<section class="page-hero page-hero-sm">
    <div class="container">
        <div class="hero-badge">
            <span class="badge-pill"><?= htmlspecialchars($post['category_name'] ?? 'General') ?></span>
        </div>
        <h1><?= htmlspecialchars($post['title']) ?></h1>
        <div class="blog-post-meta">
            <span><i class="fas fa-user"></i> <?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?></span>
            <span><i class="fas fa-clock"></i> <?= date('F j, Y', strtotime($post['published_at'] ?? $post['created_at'])) ?></span>
        </div>
    </div>
</section>

<section class="blog-single-section">
    <div class="container">
        <div class="blog-single-layout">
            <article class="blog-single-content">
                <?php if ($post['featured_image']): ?>
                    <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="blog-single-image">
                <?php endif; ?>
                <div class="blog-single-body">
                    <?= nl2br(htmlspecialchars($post['content'] ?? $post['excerpt'] ?? 'Content coming soon.')) ?>
                </div>
                <div class="blog-single-footer">
                    <div class="blog-tags">
                        <?php if ($post['tags']): ?>
                            <?php foreach (json_decode($post['tags'], true) ?? [] as $tag): ?>
                                <span class="tag"><?= htmlspecialchars($tag) ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </article>

            <aside class="blog-sidebar">
                <div class="sidebar-widget">
                    <h4>Recent Posts</h4>
                    <ul class="recent-posts">
                        <?php foreach ($recentPosts as $rp): ?>
                            <li>
                                <a href="/blog/<?= htmlspecialchars($rp['slug']) ?>">
                                    <span class="recent-post-title"><?= htmlspecialchars($rp['title']) ?></span>
                                    <span class="recent-post-date"><?= date('M j, Y', strtotime($rp['published_at'] ?? $rp['created_at'])) ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="sidebar-widget">
                    <div class="sidebar-widget-icon">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <h4>Need Medical Help?</h4>
                    <p>Book an appointment with our expert doctors. Our team is ready to provide the care you deserve.</p>
                    <a href="/contact" class="btn btn-primary btn-sm w-full">Book Now</a>
                </div>
            </aside>
        </div>
    </div>
</section>

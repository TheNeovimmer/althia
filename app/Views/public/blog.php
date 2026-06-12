<section class="page-hero">
    <div class="container">
        <div class="hero-badge">
            <span class="badge-pill">Our Blog</span>
        </div>
        <h1>Our Blog</h1>
        <p>Latest insights and wellness tips from our experts</p>
    </div>
</section>

<section class="blog-section">
    <div class="container">
        <div class="section-header centered" style="text-align:center;margin-bottom:40px;">
            <div class="section-badge centered">
                <span class="badge-pill">Latest Articles</span>
            </div>
            <h2 class="section-title">Latest Insights and Wellness Tips</h2>
            <p style="color:var(--text-muted);max-width:540px;margin:8px auto 0;">Stay informed with the latest healthcare insights, wellness tips, and updates from our team of experts.</p>
        </div>

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
                            <div class="blog-card-meta" style="display:flex;gap:16px;font-size:0.82rem;color:var(--text-muted);margin-bottom:12px;">
                                <span><i class="fas fa-user" style="color:var(--primary);"></i> <?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?></span>
                                <span><i class="fas fa-clock" style="color:var(--primary);"></i> <?= timeAgo($post['published_at'] ?? $post['created_at']) ?></span>
                            </div>
                            <a href="/blog/<?= htmlspecialchars($post['slug']) ?>" class="blog-read-more">
                                READ MORE <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="blog-grid">
                <article class="blog-card">
                    <div class="blog-card-image">
                        <img src="https://static.codia.ai/image/2026-06-11/kGika3hYkv.png" alt="Wellness">
                    </div>
                    <div class="blog-card-body">
                        <div class="blog-card-category"><span>Wellness</span></div>
                        <h3>The Science of Mindful Living in Modern Healthcare</h3>
                        <p>Discover evidence-based approaches to integrating mindfulness practices into your daily wellness routine for optimal...</p>
                        <div class="blog-card-meta" style="display:flex;gap:16px;font-size:0.82rem;color:var(--text-muted);margin-bottom:12px;">
                            <span><i class="fas fa-user" style="color:var(--primary);"></i> Althia Team</span>
                            <span><i class="fas fa-clock" style="color:var(--primary);"></i> 2 weeks ago</span>
                        </div>
                        <a href="/blog" class="blog-read-more">READ MORE <i class="fas fa-arrow-right"></i></a>
                    </div>
                </article>
                <article class="blog-card">
                    <div class="blog-card-image">
                        <img src="https://static.codia.ai/image/2026-06-11/6k25sZA5NS.png" alt="Innovation">
                    </div>
                    <div class="blog-card-body">
                        <div class="blog-card-category"><span>Innovation</span></div>
                        <h3>Digital Health Platforms: The Future of Telemedicine</h3>
                        <p>How integrated digital ecosystems are making healthcare more accessible, efficient, and patient-centered...</p>
                        <div class="blog-card-meta" style="display:flex;gap:16px;font-size:0.82rem;color:var(--text-muted);margin-bottom:12px;">
                            <span><i class="fas fa-user" style="color:var(--primary);"></i> Althia Team</span>
                            <span><i class="fas fa-clock" style="color:var(--primary);"></i> 1 month ago</span>
                        </div>
                        <a href="/blog" class="blog-read-more">READ MORE <i class="fas fa-arrow-right"></i></a>
                    </div>
                </article>
                <article class="blog-card">
                    <div class="blog-card-image">
                        <img src="https://static.codia.ai/image/2026-06-11/9pMAraxvVT.png" alt="Technology">
                    </div>
                    <div class="blog-card-body">
                        <div class="blog-card-category"><span>Technology</span></div>
                        <h3>AI-Powered Diagnostics: Transforming Patient Care</h3>
                        <p>Exploring how artificial intelligence and machine learning are revolutionizing early disease detection...</p>
                        <div class="blog-card-meta" style="display:flex;gap:16px;font-size:0.82rem;color:var(--text-muted);margin-bottom:12px;">
                            <span><i class="fas fa-user" style="color:var(--primary);"></i> Althia Team</span>
                            <span><i class="fas fa-clock" style="color:var(--primary);"></i> 2 months ago</span>
                        </div>
                        <a href="/blog" class="blog-read-more">READ MORE <i class="fas fa-arrow-right"></i></a>
                    </div>
                </article>
            </div>
        <?php endif; ?>
    </div>
</section>

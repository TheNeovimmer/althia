<section class="page-hero page-hero-sm">
    <div class="container">
        <span class="section-label">Contact Us</span>
        <h1>Let's connect with <span class="text-accent">Althia</span></h1>
        <p>Have a question about our AI health platform, services, appointments or partnerships? Our team is ready to help you.</p>
    </div>
</section>

<section class="contact-section">
    <div class="container">
        <div class="contact-grid">
            <div class="contact-info">
                <div class="contact-info-card">
                    <h3>Get In Touch</h3>
                    <p>We are here to support your health journey.</p>

                    <div class="contact-detail">
                        <div class="contact-detail-icon"><i class="fas fa-building"></i></div>
                        <div>
                            <span class="contact-label">Office</span>
                            <span>Althia Health Center, Medical City</span>
                        </div>
                    </div>
                    <div class="contact-detail">
                        <div class="contact-detail-icon"><i class="fas fa-phone"></i></div>
                        <div>
                            <span class="contact-label">Phone</span>
                            <span>+216 52721041</span>
                        </div>
                    </div>
                    <div class="contact-detail">
                        <div class="contact-detail-icon"><i class="fas fa-envelope"></i></div>
                        <div>
                            <span class="contact-label">Email</span>
                            <span>contact@medicase.health</span>
                        </div>
                    </div>
                    <div class="contact-detail">
                        <div class="contact-detail-icon"><i class="fas fa-clock"></i></div>
                        <div>
                            <span class="contact-label">Working Hours</span>
                            <span>Monday – Friday / 08:00 – 18:00</span>
                        </div>
                    </div>
                </div>

                <div class="contact-ai-card">
                    <div class="ai-mini-badge">AI</div>
                    <h4>Need quick help?</h4>
                    <p>Our AI assistant can guide you before contacting our medical support team.</p>
                    <div class="ai-chat-mini">
                        <span>Hello, how can I help you today?</span>
                    </div>
                    <a href="/register" class="btn btn-primary btn-sm">Try AI Assistant</a>
                </div>
            </div>

            <div class="contact-form-card">
                <h3>Send us a message</h3>
                <p>Fill the form and our team will contact you shortly.</p>

                <?php if (hasError('email')): ?>
                    <div class="alert alert-error"><?= htmlspecialchars(error('email')) ?></div>
                <?php endif; ?>

                <form method="POST" action="/contact">
                    <?= csrf_field() ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">First name</label>
                            <input type="text" name="first_name" class="form-input" value="<?= htmlspecialchars(old('first_name')) ?>" placeholder="Your first name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Last name</label>
                            <input type="text" name="last_name" class="form-input" value="<?= htmlspecialchars(old('last_name')) ?>" placeholder="Your last name" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-input" value="<?= htmlspecialchars(old('email')) ?>" placeholder="you@email.com" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-input" value="<?= htmlspecialchars(old('phone')) ?>" placeholder="+216">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <select name="subject" class="form-select">
                            <option value="">Choose a subject</option>
                            <option value="appointment">Appointment</option>
                            <option value="partnership">Partnership</option>
                            <option value="support">Technical Support</option>
                            <option value="feedback">Feedback</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-textarea" rows="4" placeholder="Write your message…" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Send message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="faq-section">
    <div class="container">
        <div class="section-badge centered">
            <span class="badge-pill">FAQ</span>
        </div>
        <h2 class="section-title centered">Any questions?</h2>
        <div class="faq-grid">
            <div class="faq-item">
                <h4>Can I book an appointment online?</h4>
                <p>Yes, you can contact us or use the appointment system to plan your consultation.</p>
            </div>
            <div class="faq-item">
                <h4>Is the AI assistant available 24/7?</h4>
                <p>Yes, the AI assistant can guide users at any time for basic orientation.</p>
            </div>
            <div class="faq-item">
                <h4>Do you work with clinics?</h4>
                <p>Yes, Althia supports clinics, doctors and healthcare organizations.</p>
            </div>
        </div>
    </div>
</section>

<section class="map-section">
    <div class="container">
        <div class="map-card">
            <h4>Our Location</h4>
            <p>Visit our healthcare innovation center.</p>
            <p>Our team welcomes patients, doctors and healthcare partners in a clean, secure and modern environment.</p>
        </div>
        <div class="map-placeholder">
            <div class="map-pin">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <span>Google Map / Clinic Location</span>
        </div>
    </div>
</section>

<section class="page-hero page-hero-sm">
    <div class="container">
        <div class="hero-badge">
            <span class="badge-pill">Contact Us</span>
        </div>
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
                <div class="section-badge" style="margin-bottom:12px;">
                    <span class="badge-pill">Send Message</span>
                </div>
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

<section class="faq-home-section">
    <div class="container">
        <div class="faq-header">
            <div class="section-badge centered">
                <span class="badge-pill">FAQ</span>
            </div>
            <h2 class="section-title">Any questions?</h2>
            <p>Everything you need to know about Althia. Can't find what you're looking for? <a href="/contact" style="color:var(--primary);">Contact us</a>.</p>
        </div>
        <div class="faq-accordion">
            <div class="faq-accordion-item active">
                <button class="faq-question">
                    <span>How quickly do you respond to messages?</span>
                    <span class="faq-toggle-icon"><i class="fas fa-plus"></i></span>
                </button>
                <div class="faq-answer">
                    <p>We typically respond within 24 hours during business days. Urgent inquiries are prioritized, and our AI assistant is available 24/7 for immediate assistance.</p>
                </div>
            </div>
            <div class="faq-accordion-item">
                <button class="faq-question">
                    <span>Can I book an appointment by phone?</span>
                    <span class="faq-toggle-icon"><i class="fas fa-plus"></i></span>
                </button>
                <div class="faq-answer">
                    <p>Yes, you can call us during working hours to book an appointment. You can also book online through our platform anytime.</p>
                </div>
            </div>
            <div class="faq-accordion-item">
                <button class="faq-question">
                    <span>Do you offer partnership opportunities?</span>
                    <span class="faq-toggle-icon"><i class="fas fa-plus"></i></span>
                </button>
                <div class="faq-answer">
                    <p>Yes, we welcome partnerships with clinics, hospitals, insurance companies, and healthcare technology providers.</p>
                </div>
            </div>
            <div class="faq-accordion-item">
                <button class="faq-question">
                    <span>Where are you located?</span>
                    <span class="faq-toggle-icon"><i class="fas fa-plus"></i></span>
                </button>
                <div class="faq-answer">
                    <p>Our main office is located at Althia Health Center, Medical City. We also offer fully virtual consultations through our platform.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="map-section">
    <div class="container">
        <div class="section-badge centered" style="margin-bottom:16px;">
            <span class="badge-pill">Our Location</span>
        </div>
        <div class="map-card">
            <h4>Visit our healthcare innovation center</h4>
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

    </main>
    <?php $isDashboardFtr = preg_match('#^/(patient|doctor|admin)/#', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)); ?>
    <?php if ($isDashboardFtr): ?>
    </div>
    <?php endif; ?>

    <?php if (!$isDashboardFtr): ?>
    <footer class="footer">
        <div class="footer-main">
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-brand">
                        <div class="footer-logo">
                            <img src="<?= asset('images/logo.png') ?>" alt="Althia" height="36">
                        </div>
                        <p>Transforming healthcare through technology. Connecting patients, doctors, and care teams for a better healthcare experience.</p>
                        <div class="footer-contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>123 Anywhere St., Any City, State, Country 12345</span>
                        </div>
                        <div class="footer-contact-item">
                            <i class="fas fa-phone"></i>
                            <span>(123) 456-7890</span>
                        </div>
                        <div class="footer-contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>hello@medicase.com</span>
                        </div>
                    </div>

                    <div class="footer-col">
                        <h4>Home</h4>
                        <ul>
                            <li><a href="/about">About US</a></li>
                            <li><a href="/services">Departments</a></li>
                            <li><a href="/experts">Doctors</a></li>
                        </ul>
                    </div>

                    <div class="footer-col">
                        <h4>Doctors</h4>
                        <ul>
                            <li><a href="/experts">Timetable</a></li>
                            <li><a href="/contact">Appointment</a></li>
                            <li><a href="/faq">FAQs</a></li>
                            <li><a href="/blog">Blog</a></li>
                            <li><a href="/contact">Contact Us</a></li>
                        </ul>
                    </div>

                    <div class="footer-newsletter">
                        <h4>Be Our Subscribers</h4>
                        <p>Get the latest health tips, wellness insights, and Althia updates delivered straight to your inbox.</p>
                        <form class="newsletter-form-inline" action="/newsletter" method="POST">
                            <?= csrf_field() ?>
                            <input type="email" name="email" placeholder="Enter your email address" required>
                            <button type="submit">Submit <i class="fas fa-arrow-right"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container footer-bottom-inner">
                <div class="footer-bottom-left">
                    <span>Follow Us</span>
                    <div class="footer-social">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-bottom-right">
                    &copy; <?= date('Y') ?> Althia. All rights reserved.
                </div>
            </div>
        </div>
    </footer>
    <?php endif; ?>

    <?php if (!$isDashboardFtr): ?>
    <!-- AI Chat Widget -->
    <button class="chat-widget-btn" id="chatWidgetBtn" aria-label="Open AI chat">
        <i class="fas fa-robot chat-btn-icon"></i>
        <i class="fas fa-times chat-btn-close"></i>
    </button>

    <div class="chat-widget-panel" id="chatWidgetPanel">
        <div class="chat-panel-header">
            <div class="chat-avatar">A</div>
            <div>
                <h4>Althia AI</h4>
                <p>How can I help you?</p>
            </div>
        </div>
        <div class="chat-panel-messages" id="chatMessages">
            <div class="welcome-message">
                <div class="welcome-icon"><i class="fas fa-robot"></i></div>
                <h4>Hello! I'm Althia AI</h4>
                <p>Ask me anything about our healthcare services, appointments, or general health questions.</p>
            </div>
        </div>
        <div class="chat-panel-input">
            <input type="text" id="chatInput" placeholder="Type your message..." autocomplete="off">
            <button id="chatSendBtn" aria-label="Send message"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>

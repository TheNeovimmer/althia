    </main>
    <?php $isDashboardFtr = preg_match('#^/(patient|doctor|admin)/#', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)); ?>
    <?php if ($isDashboardFtr): ?>
    </div>
    <?php endif; ?>

    <?php if (!$isDashboardFtr): ?>
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <h3>Medicase</h3>
                    <p>Transforming healthcare through technology. Connecting patients, doctors, and care teams for a better healthcare experience.</p>
                    <div class="footer-social">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <div>
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="/about">About Us</a></li>
                        <li><a href="/services">Services</a></li>
                        <li><a href="/experts">Doctors</a></li>
                        <li><a href="/blog">Blog</a></li>
                        <li><a href="/contact">Contact Us</a></li>
                    </ul>
                </div>

                <div>
                    <h4>For Patients</h4>
                    <ul class="footer-links">
                        <li><a href="/pricing">Pricing</a></li>
                        <li><a href="/blog">Health Tips</a></li>
                        <li><a href="/contact">Support</a></li>
                        <li><a href="#">FAQs</a></li>
                    </ul>
                </div>

                <div>
                    <h4>Legal</h4>
                    <ul class="footer-links">
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms & Conditions</a></li>
                        <li><a href="#">Cookie Policy</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Medicase. All rights reserved. 123 Anywhere St., Any City | (123) 456-7890 | hello@medicase.com</p>
            </div>
        </div>
    </footer>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>

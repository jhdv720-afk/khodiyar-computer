</main>
<!-- ========================== -->
<!-- PAGE CONTENT END -->
<!-- ========================== -->

<!-- ========================== -->
<!-- FOOTER -->
<!-- ========================== -->
<footer class="footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="footer-widget">
                    <h5><i class="fas fa-laptop-code me-2"></i>Khodiyar Computer</h5>
                    <p><?php echo getSetting('footer_text') ?? 'Empowering businesses with cutting-edge IT solutions.'; ?></p>
                    <div class="social-links">
                        <?php $fb = getSetting('facebook_url'); if($fb): ?>
                        <a href="<?php echo $fb; ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        <?php endif; ?>
                        <?php $ig = getSetting('instagram_url'); if($ig): ?>
                        <a href="<?php echo $ig; ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                        <?php endif; ?>
                        <?php $tw = getSetting('twitter_url'); if($tw): ?>
                        <a href="<?php echo $tw; ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                        <?php endif; ?>
                        <?php $li = getSetting('linkedin_url'); if($li): ?>
                        <a href="<?php echo $li; ?>" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                        <?php endif; ?>
                        <?php $wa = getSetting('whatsapp_number'); if($wa): ?>
                        <a href="https://wa.me/<?php echo $wa; ?>" target="_blank"><i class="fab fa-whatsapp"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <div class="footer-widget">
                    <h5>Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="services.php">Services</a></li>
                        <li><a href="book-service.php">Book Service</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="footer-widget">
                    <h5>Our Services</h5>
                    <ul class="footer-links">
                        <?php
                        $footerServices = getActiveServices();
                        foreach (array_slice($footerServices, 0, 5) as $svc):
                        ?>
                        <li><a href="service-detail.php?slug=<?php echo $svc['slug']; ?>"><?php echo htmlspecialchars($svc['title']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="footer-widget">
                    <h5>Contact Info</h5>
                    <ul class="footer-contact">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo getSetting('company_address') ?? 'Rajkot, Gujarat'; ?></span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span><?php echo getSetting('company_phone') ?? '+91 98765 43210'; ?></span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span><?php echo getSetting('company_email') ?? 'info@khodiyarcomputer.com'; ?></span>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <span><?php echo getSetting('working_hours') ?? 'Mon - Sat: 9:00 AM - 8:00 PM'; ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Khodiyar Computer. All Rights Reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Designed with <i class="fas fa-heart text-danger"></i> by Khodiyar Computer</p>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top -->
<a href="#" class="back-to-top" id="backToTop">
    <i class="fas fa-arrow-up"></i>
</a>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- AOS Animation -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<!-- Custom JS -->
<script src="js/main.js"></script>

<?php if (isset($extraJS)) echo $extraJS; ?>

<script>
    AOS.init({
        duration: 800,
        once: true,
        offset: 100
    });
</script>
</body>
</html>
<?php
// Close database connection if needed
?>


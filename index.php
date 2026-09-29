<?php
require_once 'config/init.php';
$pageTitle = 'Home';
include 'includes/header.php';

// Get some stats
$totalServices = dbFetchOne("SELECT COUNT(*) as count FROM services WHERE status = 'active'")['count'];
$totalBookings = getBookingCount('completed');
$totalUsers = getUserCount();
?>

<!-- ========================== -->
<!-- HERO SECTION -->
<!-- ========================== -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content" data-aos="fade-up">
                    <span class="hero-badge"><i class="fas fa-rocket me-2"></i>Welcome to Khodiyar Computer</span>
                    <h1>Your Trusted <span>IT Solutions</span> Partner</h1>
                    <p>We provide comprehensive digital services including web development, digital marketing, graphic design, SEO, and computer repair to help your business grow.</p>
                    <div class="hero-buttons">
                        <a href="book-service.php" class="btn btn-primary-gradient">
                            <i class="fas fa-calendar-check me-2"></i>Book a Service
                        </a>
                        <a href="services.php" class="btn btn-outline-light">
                            <i class="fas fa-cogs me-2"></i>Our Services
                        </a>
                    </div>
                    <div class="hero-stats">
                        <div class="row">
                            <div class="col-4">
                                <div class="stat-item">
                                    <h3 data-counter="<?php echo $totalServices; ?>">0</h3>
                                    <p>Services</p>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h3 data-counter="<?php echo min($totalBookings, 500); ?>">0</h3>
                                    <p>Projects Done</p>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h3 data-counter="<?php echo min($totalUsers, 200); ?>">0</h3>
                                    <p>Happy Clients</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image-wrapper" data-aos="fade-left" data-aos-delay="200">
                    <img src="https://images.unsplash.com/photo-1553877522-43269d4ea984?w=600&h=400&fit=crop" alt="IT Solutions" class="img-fluid">
                    <div class="floating-card card-1">
                        <i class="fas fa-star text-warning me-1"></i>
                        <span><strong>4.9</strong> Rating</span>
                    </div>
                    <div class="floating-card card-2">
                        <i class="fas fa-users text-primary me-1"></i>
                        <span><strong>500+</strong> Clients</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========================== -->
<!-- SERVICES SECTION -->
<!-- ========================== -->
<section class="services-section" id="services">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>Our Services</h2>
            <p>Comprehensive IT solutions tailored to your business needs</p>
        </div>
        <div class="row g-4">
            <?php
            $services = getActiveServices();
            $icons = [
                'Digital Marketing' => 'fa-chart-line',
                'Website Designing' => 'fa-paint-brush',
                'Graphic Designing' => 'fa-pen-fancy',
                'Web Development' => 'fa-code',
                'SEO Optimization' => 'fa-search',
                'Social Media Handling' => 'fa-share-alt',
                'Computer Repairing' => 'fa-tools'
            ];
            foreach ($services as $service):
                $icon = $service['icon'] ?: ($icons[$service['title']] ?? 'fa-cog');
            ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $service['display_order'] * 50; ?>">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas <?php echo $icon; ?>"></i>
                    </div>
                    <h5><?php echo htmlspecialchars($service['title']); ?></h5>
                    <p><?php echo htmlspecialchars($service['short_desc']); ?></p>
                    <?php if ($service['price_range']): ?>
                    <p class="text-primary fw-bold mb-3">Starting from <?php echo $service['price_range']; ?></p>
                    <?php endif; ?>
                    <a href="service-detail.php?slug=<?php echo $service['slug']; ?>" class="service-link">
                        Learn More <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ========================== -->
<!-- ABOUT SECTION -->
<!-- ========================== -->
<section class="about-section" id="about">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="about-image-wrapper">
                    <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=600&h=400&fit=crop" alt="About Us" class="img-fluid">
                    <div class="about-experience">
                        <h3>5+</h3>
                        <p>Years Experience</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="about-content">
                    <h2>Why Choose <span>Khodiyar Computer?</span></h2>
                    <p><?php echo getSetting('about_short') ?? 'Khodiyar Computer is a leading IT services company providing comprehensive digital solutions.'; ?></p>
                    <ul class="about-features">
                        <li><i class="fas fa-check-circle"></i> Experienced & Certified Professionals</li>
                        <li><i class="fas fa-check-circle"></i> 100% Client Satisfaction Guarantee</li>
                        <li><i class="fas fa-check-circle"></i> Affordable & Transparent Pricing</li>
                        <li><i class="fas fa-check-circle"></i> Timely Project Delivery</li>
                        <li><i class="fas fa-check-circle"></i> 24/7 Customer Support</li>
                    </ul>
                    <a href="about.php" class="btn btn-primary-gradient mt-3">
                        <i class="fas fa-info-circle me-2"></i>Learn More About Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========================== -->
<!-- WHY CHOOSE US -->
<!-- ========================== -->
<section class="why-choose-section">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>Why Choose Us</h2>
            <p>We deliver excellence through innovation and dedication</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="0">
                <div class="choose-card">
                    <div class="icon"><i class="fas fa-medal"></i></div>
                    <h5>Quality Service</h5>
                    <p>We maintain the highest quality standards in every project we deliver.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="choose-card">
                    <div class="icon"><i class="fas fa-clock"></i></div>
                    <h5>On-Time Delivery</h5>
                    <p>We respect your time and deliver projects within the committed timeline.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="choose-card">
                    <div class="icon"><i class="fas fa-hand-holding-usd"></i></div>
                    <h5>Affordable Pricing</h5>
                    <p>Get premium IT services at competitive prices that fit your budget.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="choose-card">
                    <div class="icon"><i class="fas fa-headset"></i></div>
                    <h5>24/7 Support</h5>
                    <p>Our support team is always available to assist you whenever you need.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========================== -->
<!-- CTA SECTION -->
<!-- ========================== -->
<section class="py-5" style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);">
    <div class="container text-center" data-aos="fade-up">
        <h2 class="text-white fw-bold mb-3">Ready to Get Started?</h2>
        <p class="text-white opacity-90 mb-4" style="max-width: 600px; margin: 0 auto 20px;">
            Contact us today and let's discuss how we can help your business grow with our IT solutions.
        </p>
        <div>
            <a href="book-service.php" class="btn btn-light btn-lg me-3 fw-bold">
                <i class="fas fa-calendar-check me-2"></i>Book a Service
            </a>
            <a href="contact.php" class="btn btn-outline-light btn-lg fw-bold">
                <i class="fas fa-envelope me-2"></i>Contact Us
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>


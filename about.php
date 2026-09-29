<?php
require_once 'config/init.php';
$pageTitle = 'About Us';
include 'includes/header.php';
?>

<!-- Page Banner -->
<section class="page-banner">
    <div class="container">
        <nav class="breadcrumb-nav">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">About Us</li>
            </ol>
        </nav>
        <h1 data-aos="fade-up">About Us</h1>
        <p data-aos="fade-up" data-aos-delay="100">Learn more about Khodiyar Computer and our journey</p>
    </div>
</section>

<!-- About Content -->
<section class="about-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=600&h=400&fit=crop" alt="About Khodiyar Computer" class="img-fluid rounded-4 shadow-lg">
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="about-content">
                    <h2>Welcome to <span>Khodiyar Computer</span></h2>
                    <p><?php echo getSetting('about_long') ?? 'Khodiyar Computer is a leading IT services company based in Rajkot, Gujarat. We provide comprehensive digital solutions including web development, digital marketing, graphic design, and computer repair services.'; ?></p>
                    <div class="row mt-4">
                        <div class="col-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check-circle text-primary fs-4 me-2"></i>
                                <span>Professional Team</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check-circle text-primary fs-4 me-2"></i>
                                <span>Quality Service</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check-circle text-primary fs-4 me-2"></i>
                                <span>Affordable Rates</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check-circle text-primary fs-4 me-2"></i>
                                <span>24/7 Support</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6" data-aos="fade-up">
                <div class="dashboard-card p-4 text-center h-100">
                    <div class="service-icon mx-auto" style="background: linear-gradient(135deg, rgba(108,60,225,0.1) 0%, rgba(0,210,255,0.1) 100%);">
                        <i class="fas fa-bullseye text-primary"></i>
                    </div>
                    <h4 class="fw-bold mt-3">Our Mission</h4>
                    <p class="text-muted">To empower businesses with innovative technology solutions that drive growth, efficiency, and success in the digital age.</p>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="dashboard-card p-4 text-center h-100">
                    <div class="service-icon mx-auto" style="background: linear-gradient(135deg, rgba(108,60,225,0.1) 0%, rgba(0,210,255,0.1) 100%);">
                        <i class="fas fa-eye text-primary"></i>
                    </div>
                    <h4 class="fw-bold mt-3">Our Vision</h4>
                    <p class="text-muted">To be the most trusted and preferred IT services partner, known for delivering excellence and transforming businesses through technology.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="services-section">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>Our Team</h2>
            <p>Meet the experts behind Khodiyar Computer</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up">
                <div class="service-card">
                    <img src="https://ui-avatars.com/api/?name=Khodiyar+Team&background=6C3CE1&color=fff&size=128" alt="Team" class="rounded-circle mb-3" width="100" height="100">
                    <h5>Khodiyar Team</h5>
                    <p class="text-primary fw-medium">IT Experts</p>
                    <p class="small">Dedicated professionals committed to your success.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="service-card">
                    <img src="https://ui-avatars.com/api/?name=Web+Developer&background=00D2FF&color=fff&size=128" alt="Developer" class="rounded-circle mb-3" width="100" height="100">
                    <h5>Web Developers</h5>
                    <p class="text-primary fw-medium">Development</p>
                    <p class="small">Building robust and scalable web solutions.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="service-card">
                    <img src="https://ui-avatars.com/api/?name=Designer&background=FF6B35&color=fff&size=128" alt="Designer" class="rounded-circle mb-3" width="100" height="100">
                    <h5>Creative Designers</h5>
                    <p class="text-primary fw-medium">Design</p>
                    <p class="small">Creating visually stunning designs.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="service-card">
                    <img src="https://ui-avatars.com/api/?name=Marketing+Pro&background=28A745&color=fff&size=128" alt="Marketer" class="rounded-circle mb-3" width="100" height="100">
                    <h5>Marketing Gurus</h5>
                    <p class="text-primary fw-medium">Digital Marketing</p>
                    <p class="small">Driving growth through strategic marketing.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>


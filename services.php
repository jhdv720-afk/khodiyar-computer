<?php
require_once 'config/init.php';
$pageTitle = 'Our Services';
include 'includes/header.php';

$services = getActiveServices();
?>

<!-- Page Banner -->
<section class="page-banner">
    <div class="container">
        <nav class="breadcrumb-nav">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Services</li>
            </ol>
        </nav>
        <h1 data-aos="fade-up">Our Services</h1>
        <p data-aos="fade-up" data-aos-delay="100">Comprehensive IT solutions tailored to your business needs</p>
    </div>
</section>

<!-- All Services -->
<section class="services-section">
    <div class="container">
        <div class="row g-4">
            <?php foreach ($services as $index => $service): 
                $delay = ($index % 6) * 50;
            ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                <div class="service-card">
                    <?php if (!empty($service['image'])): ?>
                    <img src="<?php echo htmlspecialchars($service['image']); ?>" alt="<?php echo htmlspecialchars($service['title']); ?>" class="img-fluid mb-3">
                    <?php endif; ?>
                    <div class="service-icon">
                        <i class="fas <?php echo $service['icon'] ?: 'fa-cog'; ?>"></i>
                    </div>
                    <h5><?php echo htmlspecialchars($service['title']); ?></h5>
                    <p><?php echo htmlspecialchars($service['short_desc']); ?></p>
                    <?php if ($service['price_range']): ?>
                    <p class="text-primary fw-bold mb-3">Starting from <?php echo $service['price_range']; ?></p>
                    <?php endif; ?>
                    <a href="service-detail.php?slug=<?php echo $service['slug']; ?>" class="btn btn-primary-gradient btn-sm">
                        <i class="fas fa-info-circle me-1"></i> Learn More
                    </a>
                    <a href="book-service.php?service=<?php echo $service['slug']; ?>" class="btn btn-outline-primary btn-sm ms-1">
                        <i class="fas fa-calendar-check me-1"></i> Book Now
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>


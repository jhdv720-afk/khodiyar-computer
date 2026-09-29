<?php
require_once 'config/init.php';

$slug = $_GET['slug'] ?? '';
$service = getServiceBySlug($slug);

if (!$service) {
    header('Location: 404.php');
    exit;
}

$pageTitle = $service['title'];
include 'includes/header.php';

$features = json_decode($service['features'], true);
?>

<!-- Page Banner -->
<section class="page-banner">
    <div class="container">
        <nav class="breadcrumb-nav">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="services.php">Services</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($service['title']); ?></li>
            </ol>
        </nav>
        <h1 data-aos="fade-up"><?php echo htmlspecialchars($service['title']); ?></h1>
    </div>
</section>

<!-- Service Detail -->
<section class="service-detail-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8" data-aos="fade-up">
                <h1><?php echo htmlspecialchars($service['title']); ?></h1>
                <?php if (!empty($service['image'])): ?>
                <img src="<?php echo htmlspecialchars($service['image']); ?>" alt="<?php echo htmlspecialchars($service['title']); ?>" class="img-fluid mb-4">
                <?php endif; ?>
                <?php echo $service['description']; ?>
                
                <?php if ($features && count($features) > 0): ?>
                <h4 class="mt-4 mb-3 fw-bold">What We Offer</h4>
                <ul class="feature-list">
                    <?php foreach ($features as $feature): ?>
                    <li><i class="fas fa-check-circle fs-5"></i> <?php echo htmlspecialchars($feature); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
            <div class="col-lg-4" data-aos="fade-left" data-aos-delay="100">
                <div class="dashboard-card p-4 sticky-top" style="top: 100px;">
                    <h5 class="fw-bold mb-3">Service Details</h5>
                    <hr>
                    <p><i class="fas fa-tag text-primary me-2"></i> <strong>Price Range:</strong> <?php echo $service['price_range'] ?? 'Contact for pricing'; ?></p>
                    <p><i class="fas fa-clock text-primary me-2"></i> <strong>Status:</strong> <span class="badge bg-success">Active</span></p>
                    <hr>
                    <a href="book-service.php?service=<?php echo $service['slug']; ?>" class="btn btn-primary-gradient w-100 mb-2">
                        <i class="fas fa-calendar-check me-2"></i>Book This Service
                    </a>
                    <a href="contact.php" class="btn btn-outline-primary w-100">
                        <i class="fas fa-envelope me-2"></i>Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>


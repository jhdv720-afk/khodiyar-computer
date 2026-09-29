<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Admin Panel - Khodiyar Computer</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Admin CSS -->
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<!-- ========================== -->
<!-- TOP NAVBAR -->
<!-- ========================== -->
<nav class="navbar navbar-dark admin-topbar fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-laptop-code me-2"></i>Khodiyar Computer
            <span class="badge bg-warning text-dark ms-2">Admin</span>
        </a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3">
                <i class="fas fa-user-shield me-1"></i>
                <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt me-1"></i>Logout
            </a>
        </div>
    </div>
</nav>

<!-- ========================== -->
<!-- SIDEBAR -->
<!-- ========================== -->
<div class="admin-sidebar">
    <div class="sidebar-header">
        <h5><i class="fas fa-tachometer-alt me-2"></i>Admin Panel</h5>
    </div>
    <ul class="sidebar-nav">
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active' : ''; ?>" href="bookings.php">
                <i class="fas fa-calendar-check"></i> Bookings
                <?php
                $pendingCount = dbFetchOne("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")['count'];
                if ($pendingCount > 0):
                ?>
                <span class="badge bg-warning text-dark float-end"><?php echo $pendingCount; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : ''; ?>" href="services.php">
                <i class="fas fa-cogs"></i> Services
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="users.php">
                <i class="fas fa-users"></i> Users
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>" href="payments.php">
                <i class="fas fa-credit-card"></i> Payments
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'testimonials.php' ? 'active' : ''; ?>" href="testimonials.php">
                <i class="fas fa-quote-right"></i> Testimonials
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'blog.php' ? 'active' : ''; ?>" href="blog.php">
                <i class="fas fa-blog"></i> Blog/News
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'newsletter.php' ? 'active' : ''; ?>" href="newsletter.php">
                <i class="fas fa-envelope-open-text"></i> Newsletter
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>" href="messages.php">
                <i class="fas fa-envelope"></i> Messages
                <?php
                $unreadCount = dbFetchOne("SELECT COUNT(*) as count FROM contacts WHERE is_read = 0")['count'];
                if ($unreadCount > 0):
                ?>
                <span class="badge bg-danger float-end"><?php echo $unreadCount; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                <i class="fas fa-cog"></i> Settings
            </a>
        </li>
        <li class="nav-item mt-4">
            <a class="nav-link text-white-50" href="../index.php" target="_blank">
                <i class="fas fa-external-link-alt"></i> View Website
            </a>
        </li>
    </ul>
</div>

<!-- ========================== -->
<!-- MAIN CONTENT -->
<!-- ========================== -->
<div class="admin-main">
    <div class="container-fluid">
        <?php displayFlashMessage(); ?>

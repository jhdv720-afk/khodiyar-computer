<?php
require_once '../config/init.php';
requireAdminLogin();

$pageTitle = 'Dashboard';
include 'includes/header.php';

// Statistics
$totalUsers = getUserCount();
$totalBookings = dbFetchOne("SELECT COUNT(*) as count FROM bookings")['count'];
$pendingBookings = dbFetchOne("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")['count'];
$completedBookings = dbFetchOne("SELECT COUNT(*) as count FROM bookings WHERE status = 'completed'")['count'];
$totalServices = dbFetchOne("SELECT COUNT(*) as count FROM services")['count'];
$totalMessages = dbFetchOne("SELECT COUNT(*) as count FROM contacts")['count'];
$unreadMessages = dbFetchOne("SELECT COUNT(*) as count FROM contacts WHERE is_read = 0")['count'];

// Recent Bookings
$recentBookings = getRecentBookings(8);

// Booking stats by status for the last 7 days
$bookingStats = dbFetchAll(
    "SELECT DATE(created_at) as date, COUNT(*) as count 
     FROM bookings 
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
     GROUP BY DATE(created_at) 
     ORDER BY date"
);
?>

<div class="page-header">
    <h4><i class="fas fa-tachometer-alt me-2 text-primary"></i>Dashboard</h4>
    <div>
        <span class="text-muted small">
            <i class="fas fa-calendar me-1"></i><?php echo date('l, d M Y'); ?>
        </span>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #6C3CE1, #8B5CF6);">
                <i class="fas fa-calendar-check text-white"></i>
            </div>
            <h3><?php echo $totalBookings; ?></h3>
            <p>Total Bookings</p>
            <small class="text-warning"><?php echo $pendingBookings; ?> pending</small>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #28a745, #34ce57);">
                <i class="fas fa-check-circle text-white"></i>
            </div>
            <h3><?php echo $completedBookings; ?></h3>
            <p>Completed</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #0dcaf0, #3dd5f3);">
                <i class="fas fa-users text-white"></i>
            </div>
            <h3><?php echo $totalUsers; ?></h3>
            <p>Registered Users</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #fd7e14, #ff9f43);">
                <i class="fas fa-envelope text-white"></i>
            </div>
            <h3><?php echo $totalMessages; ?></h3>
            <p>Messages</p>
            <?php if ($unreadMessages > 0): ?>
            <small class="text-danger"><?php echo $unreadMessages; ?> unread</small>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Bookings -->
    <div class="col-lg-8">
        <div class="content-card">
            <div class="card-header-custom">
                <h5><i class="fas fa-clock me-2 text-primary"></i>Recent Bookings</h5>
                <a href="bookings.php" class="btn btn-sm btn-primary-gradient">View All</a>
            </div>
            <?php if (count($recentBookings) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentBookings as $bk): ?>
                        <tr>
                            <td><strong><?php echo $bk['booking_id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($bk['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($bk['service_name']); ?></td>
                            <td><?php echo date('d M Y', strtotime($bk['preferred_date'])); ?></td>
                            <td>
                                <span class="badge bg-<?php echo getStatusBadge($bk['status']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $bk['status'])); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-alt"></i>
                <p>No bookings yet</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Quick Actions & Info -->
    <div class="col-lg-4">
        <div class="content-card mb-3">
            <h5 class="mb-3"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h5>
            <div class="d-grid gap-2">
                <a href="bookings.php" class="btn btn-primary-gradient">
                    <i class="fas fa-calendar-check me-2"></i>Manage Bookings
                </a>
                <a href="services.php?action=add" class="btn btn-outline-primary">
                    <i class="fas fa-plus me-2"></i>Add New Service
                </a>
                <a href="messages.php" class="btn btn-outline-primary">
                    <i class="fas fa-envelope me-2"></i>View Messages
                    <?php if ($unreadMessages > 0): ?>
                    <span class="badge bg-danger ms-1"><?php echo $unreadMessages; ?></span>
                    <?php endif; ?>
                </a>
                <a href="settings.php" class="btn btn-outline-primary">
                    <i class="fas fa-cog me-2"></i>Website Settings
                </a>
            </div>
        </div>
        
        <div class="content-card">
            <h5 class="mb-3"><i class="fas fa-info-circle me-2 text-info"></i>System Info</h5>
            <table class="table table-sm">
                <tr>
                    <td class="text-muted">PHP Version</td>
                    <td class="fw-medium"><?php echo phpversion(); ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Database</td>
                    <td class="fw-medium">MariaDB 10.4</td>
                </tr>
                <tr>
                    <td class="text-muted">Total Services</td>
                    <td class="fw-medium"><?php echo $totalServices; ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Pending Bookings</td>
                    <td class="fw-medium"><?php echo $pendingBookings; ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

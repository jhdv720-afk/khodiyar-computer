<?php
require_once 'config/init.php';
requireUserLogin();

$pageTitle = 'My Dashboard';

$user = getCurrentUser();

// Get user bookings
$bookings = dbFetchAll(
    "SELECT b.*, s.title as service_name 
     FROM bookings b 
     LEFT JOIN services s ON b.service_id = s.id 
     WHERE b.user_id = ? 
     ORDER BY b.created_at DESC",
    [$user['id']]
);

$activeTab = $_GET['page'] ?? 'dashboard';

include 'includes/header.php';
?>

<!-- Dashboard Section -->
<section class="dashboard-section">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="dashboard-card text-center mb-4">
                    <div style="font-size: 3rem; color: var(--primary);">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h5 class="fw-bold mt-2"><?php echo htmlspecialchars($user['full_name']); ?></h5>
                    <p class="text-muted small"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                
                <div class="list-group">
                    <a href="dashboard.php" class="list-group-item list-group-item-action <?php echo $activeTab === 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a href="dashboard.php?page=bookings" class="list-group-item list-group-item-action <?php echo $activeTab === 'bookings' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt me-2"></i>My Bookings
                    </a>
                    <a href="dashboard.php?page=profile" class="list-group-item list-group-item-action <?php echo $activeTab === 'profile' ? 'active' : ''; ?>">
                        <i class="fas fa-user-edit me-2"></i>Profile
                    </a>
                    <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9">
                <?php displayFlashMessage(); ?>
                
                <?php if ($activeTab === 'dashboard'): ?>
                <!-- Dashboard Overview -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="dashboard-card">
                            <div class="stat-icon" style="background: linear-gradient(135deg, #6C3CE1, #8B5CF6);">
                                <i class="fas fa-calendar-check text-white"></i>
                            </div>
                            <h3><?php echo count($bookings); ?></h3>
                            <p>Total Bookings</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-card">
                            <div class="stat-icon" style="background: linear-gradient(135deg, #28a745, #34ce57);">
                                <i class="fas fa-check-circle text-white"></i>
                            </div>
                            <h3><?php echo dbCount("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'completed'", [$user['id']]); ?></h3>
                            <p>Completed</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-card">
                            <div class="stat-icon" style="background: linear-gradient(135deg, #ffc107, #ffcd39);">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                            <h3><?php echo dbCount("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'pending'", [$user['id']]); ?></h3>
                            <p>Pending</p>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Bookings -->
                <div class="dashboard-card">
                    <h5 class="fw-bold mb-3"><i class="fas fa-clock me-2 text-primary"></i>Recent Bookings</h5>
                    <?php if (count($bookings) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-custom">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($bookings, 0, 5) as $bk): ?>
                                <tr>
                                    <td><strong><?php echo $bk['booking_id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($bk['service_name']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($bk['preferred_date'])); ?></td>
                                    <td><span class="badge bg-<?php echo getStatusBadge($bk['status']); ?>"><?php echo ucfirst($bk['status']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="dashboard.php?page=bookings" class="btn btn-outline-primary btn-sm">View All Bookings</a>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-alt fs-1 text-muted mb-2"></i>
                        <p class="text-muted">No bookings yet.</p>
                        <a href="book-service.php" class="btn btn-primary-gradient">Book a Service</a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php elseif ($activeTab === 'bookings'): ?>
                <!-- My Bookings -->
                <div class="dashboard-card">
                    <h5 class="fw-bold mb-3"><i class="fas fa-calendar-alt me-2 text-primary"></i>My Bookings</h5>
                    <?php if (count($bookings) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-custom">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Booked On</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $bk): ?>
                                <tr>
                                    <td><strong><?php echo $bk['booking_id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($bk['service_name']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($bk['preferred_date'])); ?></td>
                                    <td><?php echo $bk['preferred_time'] ? date('h:i A', strtotime($bk['preferred_time'])) : 'N/A'; ?></td>
                                    <td><span class="badge bg-<?php echo getStatusBadge($bk['status']); ?>"><?php echo ucfirst(str_replace('_', ' ', $bk['status'])); ?></span></td>
                                    <td><?php echo date('d M Y', strtotime($bk['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-alt fs-1 text-muted mb-2"></i>
                        <p class="text-muted">No bookings found.</p>
                        <a href="book-service.php" class="btn btn-primary-gradient">Book a Service Now</a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php elseif ($activeTab === 'profile'): ?>
                <!-- Profile -->
                <div class="dashboard-card">
                    <h5 class="fw-bold mb-3"><i class="fas fa-user-edit me-2 text-primary"></i>My Profile</h5>
                    
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
                        $full_name = sanitize($_POST['full_name']);
                        $phone = sanitize($_POST['phone']);
                        $address = sanitize($_POST['address'] ?? '');
                        $city = sanitize($_POST['city'] ?? '');
                        $state = sanitize($_POST['state'] ?? '');
                        $pincode = sanitize($_POST['pincode'] ?? '');
                        
                        dbQuery(
                            "UPDATE users SET full_name=?, phone=?, address=?, city=?, state=?, pincode=? WHERE id=?",
                            [$full_name, $phone, $address, $city, $state, $pincode, $user['id']]
                        );
                        
                        $_SESSION['user_name'] = $full_name;
                        echo '<div class="alert alert-success">Profile updated successfully!</div>';
                        $user = getCurrentUser();
                    }
                    ?>
                    
                    <form method="POST" action="dashboard.php?page=profile">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">State</label>
                                <input type="text" name="state" class="form-control" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pincode</label>
                                <input type="text" name="pincode" class="form-control" value="<?php echo htmlspecialchars($user['pincode'] ?? ''); ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="update_profile" class="btn btn-primary-gradient">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>


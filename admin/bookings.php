<?php
require_once '../config/init.php';
requireAdminLogin();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $bookingId = $_POST['booking_id'] ?? '';
    
    if ($_POST['action'] === 'update_status') {
        $newStatus = $_POST['status'] ?? '';
        $adminNotes = sanitize($_POST['admin_notes'] ?? '');
        
        dbQuery(
            "UPDATE bookings SET status = ?, admin_notes = ? WHERE booking_id = ?",
            [$newStatus, $adminNotes, $bookingId]
        );
        setFlashMessage('success', 'Booking status updated successfully!');
    }
    
    header('Location: bookings.php');
    exit;
}

$pageTitle = 'Manage Bookings';
include 'includes/header.php';

// Filter
$statusFilter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT b.*, s.title as service_name FROM bookings b LEFT JOIN services s ON b.service_id = s.id WHERE 1=1";
$params = [];

if ($statusFilter) {
    $sql .= " AND b.status = ?";
    $params[] = $statusFilter;
}

if ($search) {
    $sql .= " AND (b.booking_id LIKE ? OR b.customer_name LIKE ? OR b.customer_email LIKE ? OR b.customer_phone LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

$sql .= " ORDER BY b.created_at DESC";
$bookings = dbFetchAll($sql, $params);
?>

<div class="page-header">
    <h4><i class="fas fa-calendar-check me-2 text-primary"></i>Manage Bookings</h4>
    <div>
        <a href="bookings.php" class="btn btn-sm btn-primary-gradient <?php echo !$statusFilter ? 'active' : ''; ?>">All</a>
        <a href="bookings.php?status=pending" class="btn btn-sm btn-outline-warning">Pending</a>
        <a href="bookings.php?status=confirmed" class="btn btn-sm btn-outline-info">Confirmed</a>
        <a href="bookings.php?status=completed" class="btn btn-sm btn-outline-success">Completed</a>
    </div>
</div>

<div class="content-card">
    <!-- Search -->
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by name, email, phone, ID..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary-gradient w-100"><i class="fas fa-search me-1"></i>Search</button>
        </div>
        <?php if ($search || $statusFilter): ?>
        <div class="col-md-2">
            <a href="bookings.php" class="btn btn-outline-secondary w-100">Clear</a>
        </div>
        <?php endif; ?>
    </form>
    
    <?php if (count($bookings) > 0): ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $bk): ?>
                <tr>
                    <td><strong><?php echo $bk['booking_id']; ?></strong></td>
                    <td>
                        <?php echo htmlspecialchars($bk['customer_name']); ?>
                        <br><small class="text-muted"><?php echo htmlspecialchars($bk['customer_email']); ?></small>
                        <br><small class="text-muted"><?php echo htmlspecialchars($bk['customer_phone']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($bk['service_name']); ?></td>
                    <td><?php echo date('d M Y', strtotime($bk['preferred_date'])); ?></td>
                    <td><?php echo $bk['preferred_time'] ? date('h:i A', strtotime($bk['preferred_time'])) : '--'; ?></td>
                    <td>
                        <span class="badge bg-<?php echo getStatusBadge($bk['status']); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $bk['status'])); ?>
                        </span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#bookingModal<?php echo $bk['id']; ?>">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>

                <!-- Booking Detail Modal -->
                <div class="modal fade" id="bookingModal<?php echo $bk['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Booking Details - <?php echo $bk['booking_id']; ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
                                <input type="hidden" name="booking_id" value="<?php echo $bk['booking_id']; ?>">
                                <input type="hidden" name="action" value="update_status">
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($bk['customer_name']); ?></p>
                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($bk['customer_email']); ?></p>
                                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($bk['customer_phone']); ?></p>
                                            <p><strong>Address:</strong> <?php echo htmlspecialchars($bk['customer_address'] ?? 'N/A'); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Service:</strong> <?php echo htmlspecialchars($bk['service_name']); ?></p>
                                            <p><strong>Preferred Date:</strong> <?php echo date('d M Y', strtotime($bk['preferred_date'])); ?></p>
                                            <p><strong>Preferred Time:</strong> <?php echo $bk['preferred_time'] ? date('h:i A', strtotime($bk['preferred_time'])) : 'N/A'; ?></p>
                                            <p><strong>Booked On:</strong> <?php echo date('d M Y h:i A', strtotime($bk['created_at'])); ?></p>
                                        </div>
                                        <div class="col-12">
                                            <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($bk['description'] ?? 'N/A')); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Update Status</label>
                                            <select name="status" class="form-select">
                                                <option value="pending" <?php echo $bk['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="confirmed" <?php echo $bk['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                <option value="in_progress" <?php echo $bk['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                                <option value="completed" <?php echo $bk['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="cancelled" <?php echo $bk['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                <option value="rejected" <?php echo $bk['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Admin Notes</label>
                                            <textarea name="admin_notes" class="form-control" rows="3"><?php echo htmlspecialchars($bk['admin_notes'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary-gradient">
                                        <i class="fas fa-save me-1"></i>Update Status
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-calendar-alt"></i>
        <p>No bookings found</p>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

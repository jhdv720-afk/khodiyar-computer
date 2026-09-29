<?php
require_once '../config/init.php';
requireAdminLogin();

// Handle refund
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'refund') {
    $paymentId = $_POST['payment_id'] ?? '';
    $notes = sanitize($_POST['notes'] ?? '');
    
    dbQuery(
        "UPDATE payments SET payment_status = 'refunded', notes = CONCAT(IFNULL(notes, ''), '\nRefunded: ', ?) WHERE payment_id = ?",
        [$notes, $paymentId]
    );
    setFlashMessage('success', 'Payment marked as refunded successfully!');
    header('Location: payments.php');
    exit;
}

$pageTitle = 'Manage Payments';
include 'includes/header.php';

// Filters
$statusFilter = $_GET['status'] ?? '';
$methodFilter = $_GET['method'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT p.*, s.title as service_name FROM payments p 
        LEFT JOIN bookings b ON p.booking_id = b.booking_id 
        LEFT JOIN services s ON b.service_id = s.id WHERE 1=1";
$params = [];

if ($statusFilter) {
    $sql .= " AND p.payment_status = ?";
    $params[] = $statusFilter;
}
if ($methodFilter) {
    $sql .= " AND p.payment_method = ?";
    $params[] = $methodFilter;
}
if ($search) {
    $sql .= " AND (p.payment_id LIKE ? OR p.booking_id LIKE ? OR p.customer_name LIKE ? OR p.transaction_ref LIKE ?)";
    $s = "%$search%";
    $params = array_merge($params, [$s, $s, $s, $s]);
}

$sql .= " ORDER BY p.created_at DESC";
$payments = dbFetchAll($sql, $params);

// Get totals
$totalAmount = dbFetchOne("SELECT SUM(amount) as total FROM payments WHERE payment_status = 'paid'")['total'] ?? 0;
$pendingAmount = dbFetchOne("SELECT SUM(amount) as total FROM payments WHERE payment_status = 'pending'")['total'] ?? 0;
$totalTransactions = dbFetchOne("SELECT COUNT(*) as count FROM payments")['count'];
$successfulTransactions = dbFetchOne("SELECT COUNT(*) as count FROM payments WHERE payment_status = 'paid'")['count'];
?>

<div class="page-header">
    <h4><i class="fas fa-credit-card me-2 text-primary"></i>Manage Payments</h4>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #6C3CE1, #8B5CF6);">
                <i class="fas fa-credit-card text-white"></i>
            </div>
            <h3><?php echo $totalTransactions; ?></h3>
            <p>Total Transactions</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #28a745, #34ce57);">
                <i class="fas fa-check-circle text-white"></i>
            </div>
            <h3><?php echo $successfulTransactions; ?></h3>
            <p>Successful</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #28a745, #34ce57);">
                <i class="fas fa-rupee-sign text-white"></i>
            </div>
            <h3>₹<?php echo number_format($totalAmount); ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #ffc107, #ffcd39);">
                <i class="fas fa-clock text-white"></i>
            </div>
            <h3>₹<?php echo number_format($pendingAmount); ?></h3>
            <p>Pending Amount</p>
        </div>
    </div>
</div>

<div class="content-card">
    <!-- Filters -->
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="paid" <?php echo $statusFilter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="failed" <?php echo $statusFilter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                <option value="refunded" <?php echo $statusFilter === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="method" class="form-select">
                <option value="">All Methods</option>
                <option value="card" <?php echo $methodFilter === 'card' ? 'selected' : ''; ?>>Card</option>
                <option value="upi" <?php echo $methodFilter === 'upi' ? 'selected' : ''; ?>>UPI</option>
                <option value="net_banking" <?php echo $methodFilter === 'net_banking' ? 'selected' : ''; ?>>Net Banking</option>
                <option value="cash" <?php echo $methodFilter === 'cash' ? 'selected' : ''; ?>>Cash</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary-gradient w-100"><i class="fas fa-search me-1"></i>Filter</button>
        </div>
        <?php if ($search || $statusFilter || $methodFilter): ?>
        <div class="col-md-2">
            <a href="payments.php" class="btn btn-outline-secondary w-100">Clear</a>
        </div>
        <?php endif; ?>
    </form>
    
    <?php if (count($payments) > 0): ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Booking</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Transaction</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $pay): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($pay['payment_id']); ?></strong></td>
                    <td><?php echo htmlspecialchars($pay['booking_id']); ?><br><small class="text-muted"><?php echo htmlspecialchars($pay['service_name'] ?? 'N/A'); ?></small></td>
                    <td><?php echo htmlspecialchars($pay['customer_name']); ?><br><small class="text-muted"><?php echo htmlspecialchars($pay['customer_email']); ?></small></td>
                    <td class="fw-bold">₹<?php echo number_format($pay['amount']); ?></td>
                    <td><span class="badge bg-<?php echo getPaymentMethodBadge($pay['payment_method']); ?>"><?php echo ucfirst(str_replace('_', ' ', $pay['payment_method'])); ?></span></td>
                    <td><small class="text-muted"><?php echo htmlspecialchars($pay['transaction_ref'] ?? 'N/A'); ?></small></td>
                    <td>
                        <span class="badge bg-<?php echo getPaymentStatusBadge($pay['payment_status']); ?>">
                            <?php echo ucfirst($pay['payment_status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('d M Y', strtotime($pay['created_at'])); ?></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#payModal<?php echo $pay['id']; ?>">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>

                <!-- Payment Detail Modal -->
                <div class="modal fade" id="payModal<?php echo $pay['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Payment Details - <?php echo htmlspecialchars($pay['payment_id']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <table class="table table-sm">
                                    <tr><td class="text-muted">Payment ID:</td><td class="fw-bold"><?php echo htmlspecialchars($pay['payment_id']); ?></td></tr>
                                    <tr><td class="text-muted">Booking ID:</td><td><?php echo htmlspecialchars($pay['booking_id']); ?></td></tr>
                                    <tr><td class="text-muted">Customer:</td><td><?php echo htmlspecialchars($pay['customer_name']); ?><br><small><?php echo htmlspecialchars($pay['customer_email']); ?><br><?php echo htmlspecialchars($pay['customer_phone']); ?></small></td></tr>
                                    <tr><td class="text-muted">Amount:</td><td class="fw-bold fs-5 text-primary">₹<?php echo number_format($pay['amount']); ?></td></tr>
                                    <tr><td class="text-muted">Method:</td><td><span class="badge bg-<?php echo getPaymentMethodBadge($pay['payment_method']); ?>"><?php echo ucfirst(str_replace('_', ' ', $pay['payment_method'])); ?></span></td></tr>
                                    <?php if ($pay['card_last_four']): ?><tr><td class="text-muted">Card:</td><td>XXXX-<?php echo $pay['card_last_four']; ?> (<?php echo $pay['card_type']; ?>)</td></tr><?php endif; ?>
                                    <?php if ($pay['bank_name']): ?><tr><td class="text-muted">Bank:</td><td><?php echo htmlspecialchars($pay['bank_name']); ?></td></tr><?php endif; ?>
                                    <?php if ($pay['upi_id']): ?><tr><td class="text-muted">UPI ID:</td><td><?php echo htmlspecialchars($pay['upi_id']); ?></td></tr><?php endif; ?>
                                    <tr><td class="text-muted">Transaction Ref:</td><td><?php echo htmlspecialchars($pay['transaction_ref'] ?? 'N/A'); ?></td></tr>
                                    <tr><td class="text-muted">Status:</td><td><span class="badge bg-<?php echo getPaymentStatusBadge($pay['payment_status']); ?>"><?php echo ucfirst($pay['payment_status']); ?></span></td></tr>
                                    <tr><td class="text-muted">Date:</td><td><?php echo date('d M Y h:i A', strtotime($pay['created_at'])); ?></td></tr>
                                </table>
                                
                                <?php if ($pay['payment_status'] === 'paid'): ?>
                                <form method="POST" onsubmit="return confirm('Mark this payment as refunded?');">
                                    <input type="hidden" name="action" value="refund">
                                    <input type="hidden" name="payment_id" value="<?php echo $pay['payment_id']; ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Refund Notes</label>
                                        <textarea name="notes" class="form-control" rows="2" placeholder="Reason for refund..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-warning"><i class="fas fa-undo me-1"></i>Mark as Refunded</button>
                                </form>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-credit-card"></i>
        <p>No payments found</p>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>


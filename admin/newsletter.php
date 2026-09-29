<?php
require_once '../config/init.php';
requireAdminLogin();

// Handle unsubscribe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'unsubscribe') {
        $id = (int)$_POST['id'];
        dbQuery("UPDATE newsletter_subscribers SET status = 'unsubscribed', unsubscribed_at = NOW() WHERE id = ?", [$id]);
        setFlashMessage('success', 'Subscriber unsubscribed successfully.');
    } elseif ($_POST['action'] === 'delete') {
        $id = (int)$_POST['id'];
        dbQuery("DELETE FROM newsletter_subscribers WHERE id = ?", [$id]);
        setFlashMessage('success', 'Subscriber deleted successfully.');
    }
    header('Location: newsletter.php');
    exit;
}

$pageTitle = 'Newsletter Subscribers';
include 'includes/header.php';

$statusFilter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM newsletter_subscribers WHERE 1=1";
$params = [];

if ($statusFilter === 'active') {
    $sql .= " AND status = 'active'";
} elseif ($statusFilter === 'unsubscribed') {
    $sql .= " AND status = 'unsubscribed'";
}
if ($search) {
    $sql .= " AND (email LIKE ? OR name LIKE ?)";
    $s = "%$search%";
    $params[] = $s;
    $params[] = $s;
}
$sql .= " ORDER BY subscribed_at DESC";
$subscribers = dbFetchAll($sql, $params);

$totalActive = dbFetchOne("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE status = 'active'")['count'];
$totalSubscribers = dbFetchOne("SELECT COUNT(*) as count FROM newsletter_subscribers")['count'];
?>

<div class="page-header">
    <h4><i class="fas fa-envelope-open-text me-2 text-primary"></i>Newsletter Subscribers</h4>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="content-card text-center">
            <h3 class="text-primary"><?php echo $totalSubscribers; ?></h3>
            <p class="text-muted mb-0">Total Subscribers</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="content-card text-center">
            <h3 class="text-success"><?php echo $totalActive; ?></h3>
            <p class="text-muted mb-0">Active Subscribers</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="content-card text-center">
            <h3 class="text-danger"><?php echo $totalSubscribers - $totalActive; ?></h3>
            <p class="text-muted mb-0">Unsubscribed</p>
        </div>
    </div>
</div>

<div class="content-card">
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by email or name..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="unsubscribed" <?php echo $statusFilter === 'unsubscribed' ? 'selected' : ''; ?>>Unsubscribed</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary-gradient w-100"><i class="fas fa-search me-1"></i>Filter</button>
        </div>
        <?php if ($search || $statusFilter): ?>
        <div class="col-md-2">
            <a href="newsletter.php" class="btn btn-outline-secondary w-100">Clear</a>
        </div>
        <?php endif; ?>
    </form>
    
    <?php if (count($subscribers) > 0): ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Subscribed On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscribers as $sub): ?>
                <tr>
                    <td><?php echo htmlspecialchars($sub['email']); ?></td>
                    <td><?php echo htmlspecialchars($sub['name'] ?? '--'); ?></td>
                    <td>
                        <span class="badge bg-<?php echo $sub['status'] === 'active' ? 'success' : 'secondary'; ?>">
                            <?php echo ucfirst($sub['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('d M Y h:i A', strtotime($sub['subscribed_at'])); ?></td>
                    <td>
                        <?php if ($sub['status'] === 'active'): ?>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Unsubscribe this email?');">
                            <input type="hidden" name="action" value="unsubscribe">
                            <input type="hidden" name="id" value="<?php echo $sub['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-warning"><i class="fas fa-ban me-1"></i>Unsubscribe</button>
                        </form>
                        <?php endif; ?>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this subscriber permanently?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $sub['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-envelope-open-text"></i>
        <p>No subscribers found</p>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>


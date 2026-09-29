<?php
require_once '../config/init.php';
requireAdminLogin();

// Handle user status toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $userId = (int)$_POST['user_id'];
    
    if ($_POST['action'] === 'toggle_status') {
        $user = dbFetchOne("SELECT status FROM users WHERE id = ?", [$userId]);
        if ($user) {
            $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
            dbQuery("UPDATE users SET status = ? WHERE id = ?", [$newStatus, $userId]);
            setFlashMessage('success', 'User status updated successfully!');
        }
    } elseif ($_POST['action'] === 'delete') {
        dbQuery("DELETE FROM users WHERE id = ?", [$userId]);
        setFlashMessage('success', 'User deleted successfully!');
    }
    
    header('Location: users.php');
    exit;
}

$pageTitle = 'Manage Users';
include 'includes/header.php';

$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

$sql .= " ORDER BY created_at DESC";
$users = dbFetchAll($sql, $params);
?>

<div class="page-header">
    <h4><i class="fas fa-users me-2 text-primary"></i>Manage Users</h4>
</div>

<div class="content-card">
    <!-- Search -->
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by name, email, phone..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary-gradient w-100"><i class="fas fa-search me-1"></i>Search</button>
        </div>
        <?php if ($search): ?>
        <div class="col-md-2">
            <a href="users.php" class="btn btn-outline-secondary w-100">Clear</a>
        </div>
        <?php endif; ?>
    </form>
    
    <?php if (count($users) > 0): ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $index => $u): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><strong><?php echo htmlspecialchars($u['full_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><?php echo htmlspecialchars($u['phone']); ?></td>
                    <td><?php echo htmlspecialchars($u['city'] ?? '--'); ?></td>
                    <td>
                        <span class="badge bg-<?php echo getStatusBadge($u['status']); ?>">
                            <?php echo ucfirst($u['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                            <input type="hidden" name="action" value="toggle_status">
                            <button type="submit" class="btn btn-sm btn-<?php echo $u['status'] === 'active' ? 'warning' : 'success'; ?>" title="Toggle Status">
                                <i class="fas <?php echo $u['status'] === 'active' ? 'fa-ban' : 'fa-check'; ?>"></i>
                            </button>
                        </form>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this user and all their data?');">
                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                            <input type="hidden" name="action" value="delete">
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
        <i class="fas fa-users"></i>
        <p>No users found</p>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

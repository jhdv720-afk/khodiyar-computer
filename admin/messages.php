<?php
require_once '../config/init.php';
requireAdminLogin();

// Handle message actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $messageId = (int)$_POST['message_id'];
    
    if ($_POST['action'] === 'mark_read') {
        dbQuery("UPDATE contacts SET is_read = 1 WHERE id = ?", [$messageId]);
        setFlashMessage('success', 'Message marked as read.');
    } elseif ($_POST['action'] === 'mark_unread') {
        dbQuery("UPDATE contacts SET is_read = 0 WHERE id = ?", [$messageId]);
        setFlashMessage('success', 'Message marked as unread.');
    } elseif ($_POST['action'] === 'reply') {
        $reply = sanitize($_POST['reply_message']);
        dbQuery("UPDATE contacts SET replied = 1, reply_message = ?, replied_at = NOW() WHERE id = ?", [$reply, $messageId]);
        setFlashMessage('success', 'Reply sent successfully!');
    } elseif ($_POST['action'] === 'delete') {
        dbQuery("DELETE FROM contacts WHERE id = ?", [$messageId]);
        setFlashMessage('success', 'Message deleted.');
    }
    
    header('Location: messages.php');
    exit;
}

$pageTitle = 'Messages';
include 'includes/header.php';

$filter = $_GET['filter'] ?? 'all';
$sql = "SELECT * FROM contacts WHERE 1=1";
$params = [];

if ($filter === 'unread') {
    $sql .= " AND is_read = 0";
} elseif ($filter === 'read') {
    $sql .= " AND is_read = 1";
}

$sql .= " ORDER BY created_at DESC";
$messages = dbFetchAll($sql, $params);
?>

<div class="page-header">
    <h4><i class="fas fa-envelope me-2 text-primary"></i>Messages</h4>
    <div>
        <a href="messages.php?filter=all" class="btn btn-sm btn-primary-gradient">All</a>
        <a href="messages.php?filter=unread" class="btn btn-sm btn-outline-danger">Unread</a>
        <a href="messages.php?filter=read" class="btn btn-sm btn-outline-secondary">Read</a>
    </div>
</div>

<div class="content-card">
    <?php if (count($messages) > 0): ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg): ?>
                <tr class="<?php echo !$msg['is_read'] ? 'table-active fw-medium' : ''; ?>">
                    <td><?php echo date('d M Y', strtotime($msg['created_at'])); ?></td>
                    <td><?php echo htmlspecialchars($msg['name']); ?></td>
                    <td><?php echo htmlspecialchars($msg['email']); ?></td>
                    <td><?php echo htmlspecialchars($msg['subject'] ?: 'No Subject'); ?></td>
                    <td>
                        <?php if (!$msg['is_read']): ?>
                        <span class="badge bg-danger">Unread</span>
                        <?php else: ?>
                        <span class="badge bg-secondary">Read</span>
                        <?php endif; ?>
                        <?php if ($msg['replied']): ?>
                        <br><span class="badge bg-success mt-1">Replied</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#msgModal<?php echo $msg['id']; ?>">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>

                <!-- Message Modal -->
                <div class="modal fade" id="msgModal<?php echo $msg['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Message from <?php echo htmlspecialchars($msg['name']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($msg['name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($msg['email']); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($msg['phone'] ?? 'N/A'); ?></p>
                                <p><strong>Subject:</strong> <?php echo htmlspecialchars($msg['subject'] ?? 'N/A'); ?></p>
                                <p><strong>Date:</strong> <?php echo date('d M Y h:i A', strtotime($msg['created_at'])); ?></p>
                                <hr>
                                <p><strong>Message:</strong></p>
                                <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                
                                <?php if ($msg['replied']): ?>
                                <hr>
                                <p><strong>Your Reply:</strong></p>
                                <p><?php echo nl2br(htmlspecialchars($msg['reply_message'])); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!$msg['replied']): ?>
                                <hr>
                                <form method="POST">
                                    <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                    <input type="hidden" name="action" value="reply">
                                    <div class="mb-3">
                                        <label class="form-label">Reply Message</label>
                                        <textarea name="reply_message" class="form-control" rows="3" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary-gradient">
                                        <i class="fas fa-reply me-1"></i>Send Reply
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                    <input type="hidden" name="action" value="<?php echo $msg['is_read'] ? 'mark_unread' : 'mark_read'; ?>">
                                    <button type="submit" class="btn btn-sm btn-info">
                                        <i class="fas <?php echo $msg['is_read'] ? 'fa-eye-slash' : 'fa-eye'; ?> me-1"></i>
                                        Mark as <?php echo $msg['is_read'] ? 'Unread' : 'Read'; ?>
                                    </button>
                                </form>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this message?');">
                                    <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash me-1"></i>Delete
                                    </button>
                                </form>
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
        <i class="fas fa-envelope"></i>
        <p>No messages found</p>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

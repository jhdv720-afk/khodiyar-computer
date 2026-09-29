<?php
require_once '../config/init.php';
requireAdminLogin();

$pageTitle = 'Manage Testimonials';
include 'includes/header.php';

// Handle Add/Edit/Delete
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add' || $action === 'edit') {
            $client_name = sanitize($_POST['client_name']);
            $client_designation = sanitize($_POST['client_designation'] ?? '');
            $client_company = sanitize($_POST['client_company'] ?? '');
            $rating = (int)($_POST['rating'] ?? 5);
            $testimonial_text = sanitize($_POST['testimonial_text']);
            $service_id = !empty($_POST['service_id']) ? (int)$_POST['service_id'] : null;
            $display_order = (int)($_POST['display_order'] ?? 0);
            $status = $_POST['status'] ?? 'active';
            $id = $action === 'edit' ? (int)($_POST['id'] ?? 0) : 0;
            $currentTestimonial = $id ? dbFetchOne("SELECT client_image FROM testimonials WHERE id = ?", [$id]) : null;
            $client_image = uploadImageField('client_image', $currentTestimonial['client_image'] ?? '');
            
            if ($action === 'add') {
                dbInsert(
                    "INSERT INTO testimonials (client_name, client_designation, client_company, client_image, rating, testimonial_text, service_id, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$client_name, $client_designation, $client_company, $client_image, $rating, $testimonial_text, $service_id, $display_order, $status]
                );
                $message = 'Testimonial added successfully!';
                $messageType = 'success';
            } else {
                dbQuery(
                    "UPDATE testimonials SET client_name=?, client_designation=?, client_company=?, client_image=?, rating=?, testimonial_text=?, service_id=?, display_order=?, status=? WHERE id=?",
                    [$client_name, $client_designation, $client_company, $client_image, $rating, $testimonial_text, $service_id, $display_order, $status, $id]
                );
                $message = 'Testimonial updated successfully!';
                $messageType = 'success';
            }
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            dbQuery("DELETE FROM testimonials WHERE id = ?", [$id]);
            $message = 'Testimonial deleted successfully!';
            $messageType = 'success';
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}

$testimonials = dbFetchAll("SELECT t.*, s.title as service_name FROM testimonials t LEFT JOIN services s ON t.service_id = s.id ORDER BY t.display_order ASC, t.created_at DESC");
$services = getActiveServices();
$editTestimonial = null;

if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editTestimonial = dbFetchOne("SELECT * FROM testimonials WHERE id = ?", [(int)$_GET['id']]);
}
?>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="page-header">
    <h4><i class="fas fa-quote-right me-2 text-primary"></i>Manage Testimonials</h4>
    <button type="button" class="btn btn-primary-gradient" data-bs-toggle="modal" data-bs-target="#testimonialModal">
        <i class="fas fa-plus me-1"></i>Add Testimonial
    </button>
</div>

<div class="content-card">
    <?php if (count($testimonials) > 0): ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Photo</th>
                    <th>Client</th>
                    <th>Rating</th>
                    <th>Service</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($testimonials as $t): ?>
                <tr>
                    <td><?php echo $t['display_order']; ?></td>
                    <td><?php if ($t['client_image']): ?><img src="<?php echo htmlspecialchars($t['client_image']); ?>" alt="" width="40" height="40" class="rounded-circle" style="object-fit: cover;"><?php else: ?>--<?php endif; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($t['client_name']); ?></strong>
                        <?php if ($t['client_designation']): ?><br><small class="text-muted"><?php echo htmlspecialchars($t['client_designation']); ?><?php echo $t['client_company'] ? ' at ' . htmlspecialchars($t['client_company']) : ''; ?></small><?php endif; ?>
                    </td>
                    <td>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= $t['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                        <?php endfor; ?>
                    </td>
                    <td><?php echo htmlspecialchars($t['service_name'] ?? 'All Services'); ?></td>
                    <td>
                        <span class="badge bg-<?php echo getStatusBadge($t['status']); ?>"><?php echo ucfirst($t['status']); ?></span>
                    </td>
                    <td><?php echo date('d M Y', strtotime($t['created_at'])); ?></td>
                    <td>
                        <a href="testimonials.php?action=edit&id=<?php echo $t['id']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this testimonial?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
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
        <i class="fas fa-quote-right"></i>
        <p>No testimonials yet</p>
    </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Testimonial Modal -->
<div class="modal fade" id="testimonialModal" tabindex="-1" <?php echo $editTestimonial ? 'data-bs-show="true"' : ''; ?>>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo $editTestimonial ? 'Edit Testimonial' : 'Add New Testimonial'; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $editTestimonial ? 'edit' : 'add'; ?>">
                <?php if ($editTestimonial): ?>
                <input type="hidden" name="id" value="<?php echo $editTestimonial['id']; ?>">
                <?php endif; ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Client Name <span class="text-danger">*</span></label>
                            <input type="text" name="client_name" class="form-control" value="<?php echo $editTestimonial ? htmlspecialchars($editTestimonial['client_name']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rating</label>
                            <select name="rating" class="form-select">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($editTestimonial && $editTestimonial['rating'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?> Star<?php echo $i > 1 ? 's' : ''; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Designation</label>
                            <input type="text" name="client_designation" class="form-control" value="<?php echo $editTestimonial ? htmlspecialchars($editTestimonial['client_designation']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Company</label>
                            <input type="text" name="client_company" class="form-control" value="<?php echo $editTestimonial ? htmlspecialchars($editTestimonial['client_company']) : ''; ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Client Photo</label>
                            <input type="file" name="client_image" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp">
                            <?php if ($editTestimonial && $editTestimonial['client_image']): ?><img src="<?php echo htmlspecialchars($editTestimonial['client_image']); ?>" alt="Current client photo" class="rounded-circle mt-2" width="72" height="72" style="object-fit: cover;"><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Related Service</label>
                            <select name="service_id" class="form-select">
                                <option value="">All Services</option>
                                <?php foreach ($services as $svc): ?>
                                <option value="<?php echo $svc['id']; ?>" <?php echo ($editTestimonial && $editTestimonial['service_id'] == $svc['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($svc['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" class="form-control" value="<?php echo $editTestimonial ? $editTestimonial['display_order'] : '0'; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?php echo ($editTestimonial && $editTestimonial['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($editTestimonial && $editTestimonial['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Testimonial Text <span class="text-danger">*</span></label>
                            <textarea name="testimonial_text" class="form-control" rows="4" required><?php echo $editTestimonial ? htmlspecialchars($editTestimonial['testimonial_text']) : ''; ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-gradient"><i class="fas fa-save me-1"></i><?php echo $editTestimonial ? 'Update' : 'Add'; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($editTestimonial): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = new bootstrap.Modal(document.getElementById('testimonialModal'));
    modal.show();
});
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>


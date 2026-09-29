<?php
require_once '../config/init.php';
requireAdminLogin();

$pageTitle = 'Manage Services';
include 'includes/header.php';

// Handle Add/Edit/Delete
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add' || $action === 'edit') {
            $title = sanitize($_POST['title']);
            $slug = sanitize($_POST['slug']) ?: createSlug($title);
            $short_desc = sanitize($_POST['short_desc'] ?? '');
            $description = $_POST['description'] ?? '';
            $icon = sanitize($_POST['icon'] ?? 'fas fa-cog');
            $price_range = sanitize($_POST['price_range'] ?? '');
            $features = $_POST['features'] ?? '';
            $display_order = (int)($_POST['display_order'] ?? 0);
            $status = $_POST['status'] ?? 'active';
            $id = $action === 'edit' ? (int)($_POST['id'] ?? 0) : 0;
            $currentService = $id ? dbFetchOne("SELECT image FROM services WHERE id = ?", [$id]) : null;
            $image = uploadImageField('image', $currentService['image'] ?? '');
            
            // Convert features to array
            $featuresArray = array_filter(array_map('trim', explode("\n", $features)));
            $featuresJson = json_encode(array_values($featuresArray));
            
            if ($action === 'add') {
                // Check unique slug
                $existing = dbFetchOne("SELECT id FROM services WHERE slug = ?", [$slug]);
                if ($existing) {
                    $slug = $slug . '-' . time();
                }
                
                dbInsert(
                    "INSERT INTO services (title, slug, short_desc, description, icon, image, price_range, features, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$title, $slug, $short_desc, $description, $icon, $image, $price_range, $featuresJson, $display_order, $status]
                );
                $message = 'Service added successfully!';
                $messageType = 'success';
            } else {
                dbQuery(
                    "UPDATE services SET title=?, slug=?, short_desc=?, description=?, icon=?, image=?, price_range=?, features=?, display_order=?, status=? WHERE id=?",
                    [$title, $slug, $short_desc, $description, $icon, $image, $price_range, $featuresJson, $display_order, $status, $id]
                );
                $message = 'Service updated successfully!';
                $messageType = 'success';
            }
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            dbQuery("DELETE FROM services WHERE id = ?", [$id]);
            $message = 'Service deleted successfully!';
            $messageType = 'success';
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}

$services = dbFetchAll("SELECT * FROM services ORDER BY display_order ASC, id ASC");
$editService = null;

if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editService = getServiceById((int)$_GET['id']);
}
?>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="page-header">
    <h4><i class="fas fa-cogs me-2 text-primary"></i>Manage Services</h4>
    <button type="button" class="btn btn-primary-gradient" data-bs-toggle="modal" data-bs-target="#serviceModal">
        <i class="fas fa-plus me-1"></i>Add New Service
    </button>
</div>

<div class="content-card">
    <?php if (count($services) > 0): ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>Price Range</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $svc): ?>
                <tr>
                    <td><?php echo $svc['display_order']; ?></td>
                    <td><?php if ($svc['image']): ?><img src="<?php echo htmlspecialchars($svc['image']); ?>" alt="" width="48" height="36" style="object-fit: cover;"><?php else: ?>--<?php endif; ?></td>
                    <td><i class="fas <?php echo $svc['icon'] ?: 'fa-cog'; ?> me-2 text-primary"></i><?php echo htmlspecialchars($svc['title']); ?></td>
                    <td><code><?php echo $svc['slug']; ?></code></td>
                    <td><?php echo $svc['price_range'] ?: '--'; ?></td>
                    <td>
                        <span class="badge bg-<?php echo getStatusBadge($svc['status']); ?>">
                            <?php echo ucfirst($svc['status']); ?>
                        </span>
                    </td>
                    <td>
                        <a href="services.php?action=edit&id=<?php echo $svc['id']; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this service?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $svc['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-cogs"></i>
        <p>No services yet</p>
    </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Service Modal -->
<div class="modal fade" id="serviceModal" tabindex="-1" <?php echo $editService ? 'data-bs-show="true"' : ''; ?>>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo $editService ? 'Edit Service' : 'Add New Service'; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $editService ? 'edit' : 'add'; ?>">
                <?php if ($editService): ?>
                <input type="hidden" name="id" value="<?php echo $editService['id']; ?>">
                <?php endif; ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="<?php echo $editService ? htmlspecialchars($editService['title']) : ''; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control" value="<?php echo $editService ? htmlspecialchars($editService['slug']) : ''; ?>" placeholder="Auto-generated if empty">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Icon Class</label>
                            <input type="text" name="icon" class="form-control" value="<?php echo $editService ? htmlspecialchars($editService['icon']) : 'fas fa-cog'; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Price Range</label>
                            <input type="text" name="price_range" class="form-control" value="<?php echo $editService ? htmlspecialchars($editService['price_range']) : ''; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" class="form-control" value="<?php echo $editService ? $editService['display_order'] : '0'; ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Service Image</label>
                            <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp">
                            <?php if ($editService && $editService['image']): ?><img src="<?php echo htmlspecialchars($editService['image']); ?>" alt="Current service image" class="mt-2" width="120" height="80" style="object-fit: cover;"><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?php echo ($editService && $editService['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($editService && $editService['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Short Description</label>
                            <textarea name="short_desc" class="form-control" rows="2"><?php echo $editService ? htmlspecialchars($editService['short_desc']) : ''; ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Full Description (HTML)</label>
                            <textarea name="description" class="form-control" rows="4"><?php echo $editService ? htmlspecialchars($editService['description']) : ''; ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Features (one per line)</label>
                            <textarea name="features" class="form-control" rows="5"><?php 
                                if ($editService && $editService['features']) {
                                    $feats = json_decode($editService['features'], true);
                                    echo htmlspecialchars(implode("\n", $feats));
                                }
                            ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-gradient">
                        <i class="fas fa-save me-1"></i><?php echo $editService ? 'Update Service' : 'Add Service'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($editService): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('serviceModal'));
        modal.show();
    });
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>

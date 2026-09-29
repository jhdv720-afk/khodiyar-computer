<?php
require_once '../config/init.php';
requireAdminLogin();

$pageTitle = 'Manage Blog';
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
            $category = sanitize($_POST['category'] ?? 'General');
            $tags = sanitize($_POST['tags'] ?? '');
            $excerpt = sanitize($_POST['excerpt'] ?? '');
            $content = $_POST['content'] ?? '';
            $author = sanitize($_POST['author'] ?? 'Khodiyar Computer');
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $status = $_POST['status'] ?? 'draft';
            $id = $action === 'edit' ? (int)($_POST['id'] ?? 0) : 0;
            $currentPost = $id ? dbFetchOne("SELECT featured_image, published_at FROM blog_posts WHERE id = ?", [$id]) : null;
            $featured_image = uploadImageField('featured_image', $currentPost['featured_image'] ?? '');
            
            if ($action === 'add') {
                // Check unique slug
                $existing = dbFetchOne("SELECT id FROM blog_posts WHERE slug = ?", [$slug]);
                if ($existing) {
                    $slug = $slug . '-' . time();
                }
                
                $published_at = $status === 'published' ? date('Y-m-d H:i:s') : null;
                
                dbInsert(
                    "INSERT INTO blog_posts (title, slug, category, tags, excerpt, content, featured_image, author, is_featured, status, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$title, $slug, $category, $tags, $excerpt, $content, $featured_image, $author, $is_featured, $status, $published_at]
                );
                $message = 'Blog post created successfully!';
                $messageType = 'success';
            } else {
                // Set published_at if publishing for the first time
                $published_at = $currentPost['published_at'];
                if ($status === 'published' && !$published_at) {
                    $published_at = date('Y-m-d H:i:s');
                }
                
                dbQuery(
                    "UPDATE blog_posts SET title=?, slug=?, category=?, tags=?, excerpt=?, content=?, featured_image=?, author=?, is_featured=?, status=?, published_at=? WHERE id=?",
                    [$title, $slug, $category, $tags, $excerpt, $content, $featured_image, $author, $is_featured, $status, $published_at, $id]
                );
                $message = 'Blog post updated successfully!';
                $messageType = 'success';
            }
        } elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            dbQuery("DELETE FROM blog_posts WHERE id = ?", [$id]);
            $message = 'Blog post deleted successfully!';
            $messageType = 'success';
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}

$posts = dbFetchAll("SELECT * FROM blog_posts ORDER BY created_at DESC");
$editPost = null;

if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editPost = dbFetchOne("SELECT * FROM blog_posts WHERE id = ?", [(int)$_GET['id']]);
}
?>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="page-header">
    <h4><i class="fas fa-blog me-2 text-primary"></i>Manage Blog</h4>
    <button type="button" class="btn btn-primary-gradient" data-bs-toggle="modal" data-bs-target="#blogModal">
        <i class="fas fa-plus me-1"></i>New Post
    </button>
</div>

<div class="content-card">
    <?php if (count($posts) > 0): ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Views</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $p): ?>
                <tr>
                    <td><?php if ($p['featured_image']): ?><img src="<?php echo htmlspecialchars($p['featured_image']); ?>" alt="" width="56" height="40" style="object-fit: cover;"><?php else: ?>--<?php endif; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($p['title']); ?></strong>
                        <br><small class="text-muted"><?php echo htmlspecialchars($p['slug']); ?></small>
                    </td>
                    <td><span class="badge bg-info"><?php echo htmlspecialchars($p['category']); ?></span></td>
                    <td><?php echo htmlspecialchars($p['author']); ?></td>
                    <td><?php echo $p['views']; ?></td>
                    <td>
                        <span class="badge bg-<?php echo $p['status'] === 'published' ? 'success' : 'secondary'; ?>">
                            <?php echo ucfirst($p['status']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($p['is_featured']): ?>
                        <span class="badge bg-warning text-dark"><i class="fas fa-star me-1"></i>Featured</span>
                        <?php else: ?>
                        <span class="text-muted">--</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('d M Y', strtotime($p['created_at'])); ?></td>
                    <td>
                        <a href="blog.php?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this post?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
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
        <i class="fas fa-blog"></i>
        <p>No blog posts yet</p>
    </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Blog Modal -->
<div class="modal fade" id="blogModal" tabindex="-1" <?php echo $editPost ? 'data-bs-show="true"' : ''; ?>>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo $editPost ? 'Edit Post' : 'New Blog Post'; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $editPost ? 'edit' : 'add'; ?>">
                <?php if ($editPost): ?>
                <input type="hidden" name="id" value="<?php echo $editPost['id']; ?>">
                <?php endif; ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="<?php echo $editPost ? htmlspecialchars($editPost['title']) : ''; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control" value="<?php echo $editPost ? htmlspecialchars($editPost['slug']) : ''; ?>" placeholder="Auto-generated">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" class="form-control" value="<?php echo $editPost ? htmlspecialchars($editPost['category']) : 'General'; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Author</label>
                            <input type="text" name="author" class="form-control" value="<?php echo $editPost ? htmlspecialchars($editPost['author']) : 'Khodiyar Computer'; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="draft" <?php echo ($editPost && $editPost['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                <option value="published" <?php echo ($editPost && $editPost['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Tags (comma separated)</label>
                            <input type="text" name="tags" class="form-control" value="<?php echo $editPost ? htmlspecialchars($editPost['tags']) : ''; ?>" placeholder="e.g. technology, web, marketing">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Excerpt / Short Description</label>
                            <textarea name="excerpt" class="form-control" rows="2"><?php echo $editPost ? htmlspecialchars($editPost['excerpt']) : ''; ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Featured Image</label>
                            <input type="file" name="featured_image" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp">
                            <?php if ($editPost && $editPost['featured_image']): ?><img src="<?php echo htmlspecialchars($editPost['featured_image']); ?>" alt="Current featured image" class="mt-2" width="120" height="80" style="object-fit: cover;"><?php endif; ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Content (HTML) <span class="text-danger">*</span></label>
                            <textarea name="content" class="form-control" rows="10" required><?php echo $editPost ? htmlspecialchars($editPost['content']) : ''; ?></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_featured" class="form-check-input" id="isFeatured" value="1" <?php echo ($editPost && $editPost['is_featured']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="isFeatured">Mark as Featured Post</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-gradient"><i class="fas fa-save me-1"></i><?php echo $editPost ? 'Update Post' : 'Create Post'; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($editPost): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = new bootstrap.Modal(document.getElementById('blogModal'));
    modal.show();
});
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>


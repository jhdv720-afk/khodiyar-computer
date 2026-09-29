<?php
require_once '../config/init.php';
requireAdminLogin();

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_settings') {
    $settings = $_POST['settings'] ?? [];
    
    foreach ($settings as $key => $value) {
        $value = sanitize($value);
        $existing = dbFetchOne("SELECT id FROM settings WHERE setting_key = ?", [$key]);
        if ($existing) {
            dbQuery("UPDATE settings SET setting_value = ? WHERE setting_key = ?", [$value, $key]);
        } else {
            dbInsert("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)", [$key, $value]);
        }
    }
    
    setFlashMessage('success', 'Settings updated successfully!');
    header('Location: settings.php');
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_admin_image') {
    try {
        $adminId = (int)$_SESSION['admin_id'];
        $admin = dbFetchOne("SELECT profile_image FROM admins WHERE id = ?", [$adminId]);
        $profileImage = uploadImageField('profile_image', $admin['profile_image'] ?? '');
        dbQuery("UPDATE admins SET profile_image = ? WHERE id = ?", [$profileImage, $adminId]);
        setFlashMessage('success', 'Profile image updated successfully.');
    } catch (Exception $e) {
        setFlashMessage('error', $e->getMessage());
    }

    header('Location: settings.php');
    exit;
}

$pageTitle = 'Settings';
$currentAdmin = getCurrentAdmin();
include 'includes/header.php';

// Get all settings
$allSettings = dbFetchAll("SELECT * FROM settings ORDER BY setting_key ASC");
$settings = [];
foreach ($allSettings as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}
?>

<div class="page-header">
    <h4><i class="fas fa-cog me-2 text-primary"></i>Website Settings</h4>
</div>

<div class="content-card mb-4">
    <h5 class="mb-3">Admin Profile Image</h5>
    <form method="POST" enctype="multipart/form-data" class="row align-items-center g-3">
        <input type="hidden" name="action" value="update_admin_image">
        <div class="col-auto">
            <?php if (!empty($currentAdmin['profile_image'])): ?>
            <img src="<?php echo htmlspecialchars($currentAdmin['profile_image']); ?>" alt="Admin profile" width="64" height="64" class="rounded-circle" style="object-fit: cover;">
            <?php else: ?>
            <i class="fas fa-user-circle fa-3x text-secondary" aria-hidden="true"></i>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="profileImage">Choose an image</label>
            <input type="file" id="profileImage" name="profile_image" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary-gradient"><i class="fas fa-upload me-1"></i>Update Image</button>
        </div>
    </form>
</div>

<div class="content-card">
    <form method="POST">
        <input type="hidden" name="action" value="update_settings">
        
        <ul class="nav nav-tabs mb-4" id="settingsTabs">
            <li class="nav-item">
                <a class="nav-link active" href="#general" data-bs-toggle="tab">General</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#contact" data-bs-toggle="tab">Contact Info</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#social" data-bs-toggle="tab">Social Links</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#about" data-bs-toggle="tab">About & SEO</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#email" data-bs-toggle="tab">Email Settings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#payment" data-bs-toggle="tab">Payment Gateway</a>
            </li>
        </ul>
        
        <div class="tab-content">
            <!-- General Settings -->
            <div class="tab-pane fade show active" id="general">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="settings[company_name]" class="form-control" value="<?php echo htmlspecialchars($settings['company_name'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tagline</label>
                        <input type="text" name="settings[company_tagline]" class="form-control" value="<?php echo htmlspecialchars($settings['company_tagline'] ?? ''); ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Footer Text</label>
                        <textarea name="settings[footer_text]" class="form-control" rows="2"><?php echo htmlspecialchars($settings['footer_text'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Working Hours</label>
                        <input type="text" name="settings[working_hours]" class="form-control" value="<?php echo htmlspecialchars($settings['working_hours'] ?? ''); ?>">
            </div>
            
            <!-- Email Settings -->
            <div class="tab-pane fade" id="email">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" name="settings[smtp_host]" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>" placeholder="e.g. smtp.gmail.com">
                        <small class="text-muted">Leave empty to use PHP mail() function</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">SMTP Port</label>
                        <input type="text" name="settings[smtp_port]" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? '587'); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Encryption</label>
                        <select name="settings[smtp_encryption]" class="form-select">
                            <option value="tls" <?php echo ($settings['smtp_encryption'] ?? '') == 'tls' ? 'selected' : ''; ?>>TLS</option>
                            <option value="ssl" <?php echo ($settings['smtp_encryption'] ?? '') == 'ssl' ? 'selected' : ''; ?>>SSL</option>
                            <option value="" <?php echo empty($settings['smtp_encryption']) ? 'selected' : ''; ?>>None</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SMTP Username</label>
                        <input type="text" name="settings[smtp_user]" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_user'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SMTP Password</label>
                        <input type="password" name="settings[smtp_pass]" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_pass'] ?? ''); ?>">
                    </div>
                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> For Gmail, use "smtp.gmail.com" with port 587 (TLS). You may need to use an App Password if 2-Factor Authentication is enabled.
                        </div>
                    </div>
                </div>
            </div>
        </div>
            </div>
            
            <!-- Contact Info -->
            <div class="tab-pane fade" id="contact">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Company Email</label>
                        <input type="email" name="settings[company_email]" class="form-control" value="<?php echo htmlspecialchars($settings['company_email'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Company Phone</label>
                        <input type="text" name="settings[company_phone]" class="form-control" value="<?php echo htmlspecialchars($settings['company_phone'] ?? ''); ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Company Address</label>
                        <textarea name="settings[company_address]" class="form-control" rows="3"><?php echo htmlspecialchars($settings['company_address'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Social Links -->
            <div class="tab-pane fade" id="social">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Facebook URL</label>
                        <input type="url" name="settings[facebook_url]" class="form-control" value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Instagram URL</label>
                        <input type="url" name="settings[instagram_url]" class="form-control" value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Twitter URL</label>
                        <input type="url" name="settings[twitter_url]" class="form-control" value="<?php echo htmlspecialchars($settings['twitter_url'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">LinkedIn URL</label>
                        <input type="url" name="settings[linkedin_url]" class="form-control" value="<?php echo htmlspecialchars($settings['linkedin_url'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">WhatsApp Number (with country code, no +)</label>
                        <input type="text" name="settings[whatsapp_number]" class="form-control" value="<?php echo htmlspecialchars($settings['whatsapp_number'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            
            <!-- About & SEO -->
            <div class="tab-pane fade" id="about">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Short About Text</label>
                        <textarea name="settings[about_short]" class="form-control" rows="3"><?php echo htmlspecialchars($settings['about_short'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Long About Text</label>
                        <textarea name="settings[about_long]" class="form-control" rows="6"><?php echo htmlspecialchars($settings['about_long'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Meta Description (SEO)</label>
                        <textarea name="settings[meta_description]" class="form-control" rows="2"><?php echo htmlspecialchars($settings['meta_description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Payment Gateway Settings -->
            <div class="tab-pane fade" id="payment">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Razorpay Integration:</strong> Enter your Razorpay API keys below. The gateway supports UPI, Cards, Net Banking, and Wallets. In test mode, use the test cards from the Razorpay dashboard.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Razorpay Key ID</label>
                        <input type="text" name="settings[razorpay_key_id]" class="form-control" value="<?php echo htmlspecialchars($settings['razorpay_key_id'] ?? ''); ?>" placeholder="e.g. rzp_test_xxxxxxxxxxxx">
                        <small class="text-muted">Find this in your Razorpay Dashboard → Settings → API Keys</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Razorpay Key Secret</label>
                        <input type="password" name="settings[razorpay_key_secret]" class="form-control" value="<?php echo htmlspecialchars($settings['razorpay_key_secret'] ?? ''); ?>" placeholder="e.g. xxxxxxxxxxxxxxxxxxxxxxxx">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Mode</label>
                        <select name="settings[razorpay_test_mode]" class="form-select">
                            <option value="1" <?php echo ($settings['razorpay_test_mode'] ?? '1') == '1' ? 'selected' : ''; ?>>Test Mode</option>
                            <option value="0" <?php echo ($settings['razorpay_test_mode'] ?? '1') == '0' ? 'selected' : ''; ?>>Live Mode</option>
                        </select>
                        <small class="text-muted">Use Test Mode for development. Switch to Live Mode when going to production.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Enable Online Payment</label>
                        <select name="settings[razorpay_enabled]" class="form-select">
                            <option value="1" <?php echo ($settings['razorpay_enabled'] ?? '1') == '1' ? 'selected' : ''; ?>>Enabled</option>
                            <option value="0" <?php echo ($settings['razorpay_enabled'] ?? '1') == '0' ? 'selected' : ''; ?>>Disabled</option>
                        </select>
                        <small class="text-muted">If disabled, only "Pay at Service" will be available at checkout.</small>
                    </div>
                    <div class="col-12">
                        <hr>
                        <h6 class="fw-bold mb-3"><i class="fas fa-flask text-primary me-2"></i>Razorpay Test Card Details</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Test Card</th>
                                        <th>Number</th>
                                        <th>Expiry</th>
                                        <th>CVV</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Visa</td>
                                        <td><code>4111 1111 1111 1111</code></td>
                                        <td>Any future date</td>
                                        <td>Any 3 digits</td>
                                    </tr>
                                    <tr>
                                        <td>Mastercard</td>
                                        <td><code>5555 5555 5555 4444</code></td>
                                        <td>Any future date</td>
                                        <td>Any 3 digits</td>
                                    </tr>
                                    <tr>
                                        <td>Amex</td>
                                        <td><code>3782 822463 10005</code></td>
                                        <td>Any future date</td>
                                        <td>Any 4 digits</td>
                                    </tr>
                                    <tr>
                                        <td>UPI (Success)</td>
                                        <td colspan="3">Use UPI ID: <code>success@razorpay</code> in test mode</td>
                                    </tr>
                                    <tr>
                                        <td>UPI (Failure)</td>
                                        <td colspan="3">Use UPI ID: <code>failure@razorpay</code> in test mode</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted">OTP for all test cards: <code>1234</code></small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="btn btn-primary-gradient btn-lg">
                <i class="fas fa-save me-2"></i>Save All Settings
            </button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>

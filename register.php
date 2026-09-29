<?php
require_once 'config/init.php';
$pageTitle = 'Create Account';

// Redirect if already logged in
if (isUserLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $full_name = sanitize($_POST['full_name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $address = sanitize($_POST['address'] ?? '');
        $city = sanitize($_POST['city'] ?? '');
        $state = sanitize($_POST['state'] ?? '');
        $pincode = sanitize($_POST['pincode'] ?? '');
        
        // Validation
        if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
            throw new Exception('Please fill in all required fields.');
        }
        
        if (!validateEmail($email)) {
            throw new Exception('Please enter a valid email address.');
        }
        
        if (!preg_match('/^[0-9]{10}$/', $phone)) {
            throw new Exception('Please enter a valid 10-digit phone number.');
        }
        
        if (strlen($password) < 6) {
            throw new Exception('Password must be at least 6 characters long.');
        }
        
        if ($password !== $confirm_password) {
            throw new Exception('Passwords do not match.');
        }
        
        // Check if email already exists
        $existing = dbFetchOne("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            throw new Exception('An account with this email already exists. Please login.');
        }
        
        // Hash password and insert
        $hashedPassword = hashPassword($password);
        
        dbInsert(
            "INSERT INTO users (full_name, email, phone, password, address, city, state, pincode) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$full_name, $email, $phone, $hashedPassword, $address, $city, $state, $pincode]
        );
        
        $success = 'Account created successfully! You can now login.';
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

include 'includes/header.php';
?>

<!-- Auth Section -->
<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="auth-card" data-aos="fade-up">
                    <div class="auth-header">
                        <div style="font-size: 3rem; color: var(--primary);">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h3>Create Account</h3>
                        <p>Join Khodiyar Computer today</p>
                    </div>
                    
                    <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="login.php" class="btn btn-primary-gradient">
                            <i class="fas fa-sign-in-alt me-2"></i>Login Now
                        </a>
                    </div>
                    <?php else: ?>
                    
                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" class="form-control" maxlength="10" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">State</label>
                                <input type="text" name="state" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pincode</label>
                                <input type="text" name="pincode" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" minlength="6" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="confirm_password" class="form-control" minlength="6" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary-gradient w-100 py-3">
                                    <i class="fas fa-user-plus me-2"></i>Create Account
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p class="mb-0">Already have an account? <a href="login.php" class="text-primary fw-bold">Login here</a></p>
                    </div>
                    
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>


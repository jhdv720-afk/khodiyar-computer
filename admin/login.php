<?php
require_once '../config/init.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = sanitize($_POST['username']);
        $password = $_POST['password'];
        
        if (empty($username) || empty($password)) {
            throw new Exception('Please enter username and password.');
        }
        
        $admin = dbFetchOne("SELECT * FROM admins WHERE (username = ? OR email = ?) AND status = 'active'", [$username, $username]);
        
        if (!$admin || !verifyPassword($password, $admin['password'])) {
            throw new Exception('Invalid credentials.');
        }
        
        // Set admin session
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_username'] = $admin['username'];
        
        // Update last login
        dbQuery("UPDATE admins SET last_login = NOW() WHERE id = ?", [$admin['id']]);
        
        setFlashMessage('success', 'Welcome back, ' . $admin['full_name'] . '!');
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Khodiyar Computer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0F0C29 0%, #302B63 50%, #24243E 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-card .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-card .logo h3 {
            font-weight: 700;
            color: #0F0C29;
        }
        .login-card .logo p {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .form-control {
            padding: 12px 16px;
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        .form-control:focus {
            border-color: #6C3CE1;
            box-shadow: 0 0 0 0.2rem rgba(108, 60, 225, 0.15);
        }
        .btn-login {
            background: linear-gradient(135deg, #6C3CE1, #5A2DB8);
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #5A2DB8, #4A2598);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(108, 60, 225, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6">
                <div class="login-card" data-aos="fade-up">
                    <div class="logo">
                        <i class="fas fa-laptop-code" style="font-size: 3rem; color: #6C3CE1;"></i>
                        <h3 class="mt-2">Admin Panel</h3>
                        <p>Khodiyar Computer</p>
                    </div>
                    
                    <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Username / Email</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-login w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Login to Admin
                        </button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="../index.php" class="text-decoration-none small">
                            <i class="fas fa-arrow-left me-1"></i>Back to Website
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

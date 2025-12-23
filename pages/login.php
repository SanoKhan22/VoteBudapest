<?php
/**
 * Login Page
 * MINIMUM REQUIREMENT: Login with username + password
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
redirect_if_logged_in();

$page_title = 'Login';
$redirect = $_GET['redirect'] ?? '/index.php';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="bi bi-lock-fill" style="font-size: 30px;"></i>
                        </div>
                    </div>
                    <h2 class="card-title text-center mb-2">Welcome Back</h2>
                    <p class="text-center text-muted mb-4">Login to your VotaBudapest account</p>
                    
                    <?php if ($flash = get_flash()): ?>
                        <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show" role="alert">
                            <strong><i class="bi bi-<?php echo $flash['type'] === 'error' ? 'x-circle-fill' : 'check-circle-fill'; ?>"></i> <?php echo $flash['type'] === 'error' ? 'Error!' : 'Success!'; ?></strong>
                            <div><?php echo $flash['message']; ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form action="/actions/login-action.php" method="POST" novalidate>
                        <input type="hidden" name="redirect" value="<?php echo e($redirect); ?>">
                        
                        <!-- Username -->
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                Username <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="username" 
                                    name="username" 
                                    required
                                    autocomplete="username"
                                    autofocus
                                    placeholder="Enter your username"
                                >
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                Password <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    id="password" 
                                    name="password" 
                                    required
                                    autocomplete="current-password"
                                    placeholder="Enter your password"
                                >
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted">
                            Don't have an account? 
                            <a href="/pages/register.php" class="text-decoration-none">Register here</a>
                        </p>
                    </div>
                    
                    <div class="alert alert-info mt-4" role="alert">
                        <strong>Admin Access:</strong><br>
                        Username: <code>admin</code><br>
                        Password: <code>admin</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
/**
 * Registration Page
 * MINIMUM REQUIREMENT: Username unique, Password 8+ chars, Registration enables login
 * CORE TASKS: Username no spaces (0.5 pts), Email valid (0.5 pts), Password complexity (0.5 pts), Passwords match (0.5 pts)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
redirect_if_logged_in();

$page_title = 'Register';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="bi bi-person-plus-fill" style="font-size: 30px;"></i>
                        </div>
                    </div>
                    <h2 class="card-title text-center mb-2">Create Account</h2>
                    <p class="text-center text-muted mb-4">Join VotaBudapest and start voting on community projects</p>
                    
                    <?php if ($flash = get_flash()): ?>
                        <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show" role="alert">
                            <strong><i class="bi bi-<?php echo $flash['type'] === 'error' ? 'x-circle-fill' : 'check-circle-fill'; ?>"></i> <?php echo $flash['type'] === 'error' ? 'Error!' : 'Success!'; ?></strong>
                            <div><?php echo $flash['message']; ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form action="/actions/register-action.php" method="POST" novalidate>
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
                                    value="<?php echo e($_POST['username'] ?? ''); ?>"
                                    autocomplete="username"
                                    placeholder="Choose a username"
                                >
                            </div>
                            <div class="form-text">
                                Must be unique, no spaces allowed. Minimum 3 characters.
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                                <input 
                                    type="email" 
                                    class="form-control" 
                                    id="email" 
                                    name="email" 
                                    required
                                    value="<?php echo e($_POST['email'] ?? ''); ?>"
                                    autocomplete="email"
                                    placeholder="your.email@example.com"
                                >
                            </div>
                            <div class="form-text">
                                Must be a valid email address.
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
                                    autocomplete="new-password"
                                    placeholder="Create a strong password"
                                >
                            </div>
                            <div class="form-text">
                                Minimum 8 characters. Must include lowercase, uppercase, and numeric characters.
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">
                                Confirm Password <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password_confirm" 
                                name="password_confirm" 
                                required
                                autocomplete="new-password"
                            >
                            <div class="form-text">
                                Re-enter your password to confirm.
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle-fill"></i> Create Account
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted">
                            Already have an account? 
                            <a href="/pages/login.php" class="text-decoration-none fw-bold">Login here</a>
                        </p>
                    </div>
                        <p class="text-muted">
                            Already have an account? 
                            <a href="/pages/login.php" class="text-decoration-none">Login here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

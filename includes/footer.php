    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row g-4">
                <!-- About Section -->
                <div class="col-md-4">
                    <div class="mb-3">
                        <img src="/assets/images/logo.png" alt="<?php echo APP_NAME; ?>" style="height: 80px; width: auto; max-width: 250px;">
                    </div>
                    <p class="text-muted mb-3">
                        <?php echo APP_TAGLINE; ?>
                    </p>
                    <p class="small text-muted">
                        Empowering Budapest citizens to shape their community through participatory budgeting.
                    </p>
                </div>
                
                <!-- Quick Links -->
                <div class="col-md-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="/index.php">
                                <i class="bi bi-chevron-right me-1"></i> Browse Projects
                            </a>
                        </li>
                        <?php if (is_logged_in()): ?>
                            <li class="mb-2">
                                <a href="/pages/submit-project.php">
                                    <i class="bi bi-chevron-right me-1"></i> Submit Project
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="/pages/projects-own.php">
                                    <i class="bi bi-chevron-right me-1"></i> My Projects
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="mb-2">
                                <a href="/pages/login.php">
                                    <i class="bi bi-chevron-right me-1"></i> Login
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="/pages/register.php">
                                    <i class="bi bi-chevron-right me-1"></i> Register
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <!-- Technical Info -->
                <div class="col-md-4">
                    <h5 class="mb-3">Technical Details</h5>
                    <p class="small text-muted mb-2">
                        <i class="bi bi-code-square me-1"></i> Built with vanilla PHP (no frameworks)
                    </p>
                    <p class="small text-muted mb-2">
                        <i class="bi bi-bootstrap-fill me-1"></i> Bootstrap 5.3 for responsive design
                    </p>
                    <p class="small text-muted">
                        <i class="bi bi-database-fill me-1"></i> MySQL database with secure authentication
                    </p>
                </div>
            </div>
            
            <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">
            
            <!-- Copyright -->
            <div class="text-center">
                <p class="text-muted small mb-0">
                    © <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Feather Icons -->
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script>
        // Initialize Feather icons
        feather.replace();
    </script>
    
    <!-- AJAX Voting (EXTRA TASK: 1.0 pt) -->
    <?php if (is_logged_in() && !is_admin()): ?>
    <script src="/assets/js/vote.js"></script>
    <?php endif; ?>
</body>
</html>

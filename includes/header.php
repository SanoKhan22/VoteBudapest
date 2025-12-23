<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title ?? 'VotaBudapest'); ?> - <?php echo APP_NAME; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary: #C8102E;
            --primary-dark: #9A0C24;
            --primary-light: #E6475A;
            --secondary: #477050;
            --secondary-dark: #2F4A36;
            --border-radius: 12px;
            --border-radius-sm: 8px;
            --border-radius-lg: 16px;
        }
        
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-weight: 400;
            letter-spacing: -0.01em;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        
        .lead {
            font-weight: 400;
            letter-spacing: -0.01em;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-radius: 0;
            padding: 1rem 0;
        }
        
        .navbar-brand {
            font-weight: 800;
            font-size: 1.75rem;
            color: white !important;
            letter-spacing: -0.03em;
            transition: all 0.3s ease;
        }
        
        .navbar-brand:hover {
            transform: scale(1.05);
            opacity: 0.9;
        }
        
        .nav-link {
            font-weight: 500;
            color: rgba(255,255,255,0.9) !important;
            transition: all 0.2s ease;
            padding: 0.5rem 1rem !important;
            border-radius: var(--border-radius-sm);
        }
        
        .nav-link:hover {
            color: white !important;
            background-color: rgba(255,255,255,0.1);
        }
        
        .nav-link.active {
            background-color: rgba(255,255,255,0.15);
            color: white !important;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
        }
        
        .nav-link:hover {
            color: white !important;
        }
        
        .btn {
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            padding: 0.625rem 1.25rem;
            letter-spacing: -0.01em;
            transition: all 0.2s ease;
        }
        
        .btn-lg {
            padding: 0.875rem 2rem;
            border-radius: var(--border-radius);
        }
        
        .btn-sm {
            padding: 0.375rem 0.875rem;
            border-radius: var(--border-radius-sm);
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(200, 16, 46, 0.3);
        }
        
        .btn-secondary {
            background-color: var(--secondary);
            border-color: var(--secondary);
        }
        
        .btn-secondary:hover {
            background-color: var(--secondary-dark);
            border-color: var(--secondary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(71, 112, 80, 0.3);
        }
        
        .card {
            border-radius: var(--border-radius);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .card-header {
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
            font-weight: 600;
        }
        
        .form-control, .form-select {
            border-radius: var(--border-radius-sm);
            border: 1.5px solid #dee2e6;
            padding: 0.625rem 1rem;
            transition: all 0.2s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(200, 16, 46, 0.1);
        }
        
        .input-group {
            border-radius: var(--border-radius-sm);
        }
        
        .input-group-text {
            border-radius: var(--border-radius-sm) 0 0 var(--border-radius-sm);
            border: 1.5px solid #dee2e6;
            border-right: none;
            background-color: #f8f9fa;
            font-weight: 500;
            color: var(--primary);
        }
        
        .input-group > .form-control {
            border-left: none;
            border-radius: 0 var(--border-radius-sm) var(--border-radius-sm) 0;
        }
        
        .input-group > .form-control:focus {
            border-left: 1.5px solid var(--primary);
        }
        
        .progress {
            border-radius: var(--border-radius-sm);
        }
        
        main {
            flex: 1;
        }
        
        footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            padding: 3rem 0 2rem;
            margin-top: 5rem;
            border-top: 4px solid var(--primary);
            color: #ecf0f1;
            position: relative;
            overflow: hidden;
        }
        
        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('/assets/images/footer.png');
            background-size: cover;
            background-position: center;
            opacity: 0.08;
            pointer-events: none;
        }
        
        footer h5 {
            color: white;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        
        footer a {
            color: #bdc3c7;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        footer a:hover {
            color: var(--primary-light);
        }
        
        footer .text-muted {
            color: #95a5a6 !important;
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(200, 16, 46, 0.05) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .min-vh-60 {
            min-height: 60vh;
        }
        
        /* Stats Cards */
        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        
        /* Filter Form */
        .filter-form {
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        
        /* Category Section */
        .category-section {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .category-header {
            position: relative;
        }
        
        .category-icon {
            display: inline-block;
            transition: transform 0.3s ease;
        }
        
        .category-section:hover .category-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .voting-progress {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: var(--border-radius-sm);
            border: 1px solid #e9ecef;
        }
        
        .project-card {
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            height: 100%;
            border: none !important;
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        
        .project-card:hover, .hover-shadow:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 48px rgba(200, 16, 46, 0.15) !important;
        }
        
        .project-card .card-img-top {
            border-radius: 0;
            transition: transform 0.4s ease;
        }
        
        .project-card:hover .card-img-top {
            transform: scale(1.05);
        }
        
        .project-card .card-body {
            padding: 1.75rem;
        }
        
        .project-card .card-title {
            font-weight: 700;
            font-size: 1.25rem;
            line-height: 1.3;
            margin-bottom: 1rem;
        }
        
        .project-card .card-title a {
            color: #2c3e50;
            transition: color 0.2s ease;
        }
        
        .project-card .card-title a:hover {
            color: var(--primary);
        }
        
        .badge-pending { background-color: #FF9800; }
        .badge-approved { background-color: #4CAF50; }
        .badge-rejected { background-color: #F44336; }
        .badge-rework { background-color: #2196F3; }
        
        .vote-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        /* Smooth animations */
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Card improvements */
        .card {
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .vote-btn.voted {
            background-color: var(--primary);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/index.php">
                <img src="/assets/images/logo.png" alt="<?php echo APP_NAME; ?>" style="height: 60px; width: auto; max-width: 200px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/index.php">
                            <i class="bi bi-house-fill me-1"></i> Home
                        </a>
                    </li>
                    
                    <?php if (is_logged_in()): ?>
                        <!-- Logged-in user menu -->
                        <li class="nav-item">
                            <a class="nav-link" href="/pages/submit-project.php">
                                <i class="bi bi-plus-circle-fill me-1"></i> Submit Project
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/pages/projects-own.php">
                                <i class="bi bi-folder-fill me-1"></i> My Projects
                            </a>
                        </li>
                        
                        <?php if (is_admin()): ?>
                            <!-- Admin menu -->
                            <li class="nav-item">
                                <a class="nav-link" href="/pages/admin/projects-admin.php">
                                    <i class="bi bi-shield-fill-check me-1"></i> Admin Panel
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/pages/admin/statistics.php">
                                    <i class="bi bi-graph-up-arrow me-1"></i> Statistics
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i> <?php echo e(get_username()); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="/actions/logout-action.php">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Guest menu -->
                        <li class="nav-item">
                            <a class="nav-link" href="/pages/login.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-light text-dark ms-2 px-3 rounded-pill" href="/pages/register.php">
                                <i class="bi bi-person-plus-fill me-1"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if ($flash = get_flash()): ?>
        <div class="container mt-3">
            <?php echo display_flash(); ?>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="py-4">
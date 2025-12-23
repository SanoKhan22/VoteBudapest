<?php
/**
 * Homepage (index.php)
 * MINIMUM REQUIREMENT: Display published projects, filterable by category
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Home';

// Get category filter from URL
$category_filter = $_GET['category'] ?? 'all';

require_once __DIR__ . '/includes/header.php';
?>

<div class="hero-section mb-5">
    <div class="container">
        <div class="row align-items-center min-vh-60">
            <div class="col-lg-6 text-center text-lg-start py-5">
                <span class="badge bg-primary mb-3 px-3 py-2" style="font-size: 0.9rem;">
                    <i class="bi bi-star-fill me-1"></i> Participatory Budgeting Platform
                </span>
                <h1 class="display-2 fw-bold mb-4" style="line-height: 1.1;">
                    Shape <span style="color: var(--primary);">Budapest's</span> Future Together
                </h1>
                <p class="lead mb-4" style="font-size: 1.25rem; color: #555; line-height: 1.7;">
                    Join thousands of residents in deciding how our community budget is spent. Submit your ideas, vote on projects, and make a real difference.
                </p>
                <?php if (!is_logged_in()): ?>
                    <div class="d-flex gap-3 justify-content-center justify-content-lg-start">
                        <a href="/pages/register.php" class="btn btn-primary btn-lg px-5 py-3 shadow-lg">
                            <i class="bi bi-person-plus-fill me-2"></i> Get Started
                        </a>
                        <a href="/pages/login.php" class="btn btn-outline-primary btn-lg px-5 py-3">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Login
                        </a>
                    </div>
                <?php else: ?>
                    <div class="d-flex gap-3 justify-content-center justify-content-lg-start">
                        <a href="/pages/submit-project.php" class="btn btn-primary btn-lg px-5 py-3 shadow-lg">
                            <i class="bi bi-plus-circle-fill me-2"></i> Submit Your Idea
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="position-relative">
                    <div class="hero-illustration p-5">
                        <div class="text-center">
                            <i class="bi bi-building" style="font-size: 12rem; color: var(--primary); opacity: 0.1;"></i>
                            <div class="position-absolute top-50 start-50 translate-middle">
                                <i class="bi bi-hand-thumbs-up-fill" style="font-size: 8rem; color: var(--primary);"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">

    <!-- Stats Bar -->
    <?php
    try {
        $db = getDB();
        $stats_stmt = $db->query("SELECT 
            (SELECT COUNT(*) FROM projects WHERE status_id = " . STATUS_APPROVED . ") as total_projects,
            (SELECT COUNT(*) FROM votes) as total_votes,
            (SELECT COUNT(*) FROM users WHERE is_admin = 0) as total_users
        ");
        $stats = $stats_stmt->fetch();
    ?>
    <div class="row mb-5">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="stat-card text-center p-4 h-100">
                <i class="bi bi-folder-fill text-primary mb-2" style="font-size: 2.5rem;"></i>
                <h3 class="display-6 fw-bold mb-0"><?php echo $stats['total_projects']; ?></h3>
                <p class="text-muted mb-0">Active Projects</p>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="stat-card text-center p-4 h-100">
                <i class="bi bi-hand-thumbs-up-fill text-success mb-2" style="font-size: 2.5rem;"></i>
                <h3 class="display-6 fw-bold mb-0"><?php echo $stats['total_votes']; ?></h3>
                <p class="text-muted mb-0">Total Votes</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card text-center p-4 h-100">
                <i class="bi bi-people-fill text-info mb-2" style="font-size: 2.5rem;"></i>
                <h3 class="display-6 fw-bold mb-0"><?php echo $stats['total_users']; ?></h3>
                <p class="text-muted mb-0">Community Members</p>
            </div>
        </div>
    </div>
    <?php } catch (PDOException $e) {} ?>

    <!-- Category Filter -->
    <div class="row mb-5">
        <div class="col-12">
            <form method="GET" action="/index.php" class="filter-form">
                <div class="d-flex align-items-center gap-3">
                    <label class="fw-bold text-muted mb-0" style="white-space: nowrap;">
                        <i class="bi bi-funnel-fill me-2"></i>Filter by:
                    </label>
                    <select class="form-select form-select-lg" id="category" name="category" onchange="this.form.submit()" style="max-width: 300px;">
                        <option value="all" <?php echo $category_filter === 'all' ? 'selected' : ''; ?>>
                            All Categories
                        </option>
                        <?php
                        try {
                            $db = getDB();
                            $stmt = $db->query("SELECT id, name FROM categories ORDER BY id");
                            while ($cat = $stmt->fetch()) {
                                $selected = $category_filter == $cat['id'] ? 'selected' : '';
                                echo '<option value="' . $cat['id'] . '" ' . $selected . '>' . 
                                     e($cat['name']) . '</option>';
                            }
                        } catch (PDOException $e) {
                            echo '<option value="">Error loading categories</option>';
                        }
                        ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Projects Grouped by Category -->
    <?php
    try {
        $db = getDB();
        
        // Get user's votes if logged in
        $user_votes = [];
        $votes_per_category = [];
        if (is_logged_in()) {
            $stmt = $db->prepare("
                SELECT v.project_id, p.category_id
                FROM votes v
                JOIN projects p ON v.project_id = p.id
                WHERE v.user_id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $rows = $stmt->fetchAll();
            
            foreach ($rows as $row) {
                $user_votes[] = $row['project_id'];
                $cat_id = $row['category_id'];
                $votes_per_category[$cat_id] = ($votes_per_category[$cat_id] ?? 0) + 1;
            }
        }
        
        // Fetch categories
        $stmt = $db->query("SELECT id, name FROM categories ORDER BY id");
        $categories = $stmt->fetchAll();
        
        $has_projects = false;
        
        foreach ($categories as $cat) {
            // Skip if filter is set and doesn't match this category
            if ($category_filter !== 'all' && $category_filter != $cat['id']) {
                continue;
            }
            
            // Fetch projects for this category
            $sql = "SELECT p.id, p.title, p.description, p.image_path, p.approved_at, p.category_id, p.postal_code,
                           u.username as owner_username,
                           c.name as category_name,
                           (SELECT COUNT(*) FROM votes WHERE project_id = p.id) as vote_count
                    FROM projects p
                    JOIN users u ON p.user_id = u.id
                    JOIN categories c ON p.category_id = c.id
                    WHERE p.status_id = ? AND p.category_id = ?
                    ORDER BY p.approved_at DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([STATUS_APPROVED, $cat['id']]);
            $projects = $stmt->fetchAll();
            
            if (empty($projects)) continue;
            
            $has_projects = true;
            
            // Calculate remaining votes for this category
            $remaining_votes = is_logged_in() ? 
                MAX_VOTES_PER_CATEGORY - ($votes_per_category[$cat['id']] ?? 0) : 0;
            ?>
            
            <!-- Category Section -->
            <div class="category-section mb-5" data-category="<?php echo $cat['id']; ?>">
                <div class="category-header mb-4 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h2 class="h2 mb-2 fw-bold">
                                <?php echo e($cat['name']); ?>
                            </h2>
                            <p class="text-muted mb-0">Discover projects in this category</p>
                        </div>
                        <?php if (is_logged_in()): 
                            $votes_used = MAX_VOTES_PER_CATEGORY - $remaining_votes;
                            $vote_percentage = ($votes_used / MAX_VOTES_PER_CATEGORY) * 100;
                        ?>
                            <div class="voting-progress" style="min-width: 280px;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="fw-bold text-muted">
                                        <i class="bi bi-hand-thumbs-up-fill me-1"></i>
                                        <span class="remaining-votes"><?php echo $remaining_votes; ?></span>/<?php echo MAX_VOTES_PER_CATEGORY; ?> votes left
                                    </small>
                                    <small class="text-muted"><?php echo $votes_used; ?> used</small>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar <?php echo $remaining_votes === 0 ? 'bg-danger' : 'bg-success'; ?>" 
                                         role="progressbar" 
                                         style="width: <?php echo $vote_percentage; ?>%"
                                         aria-valuenow="<?php echo $votes_used; ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="<?php echo MAX_VOTES_PER_CATEGORY; ?>">
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Projects Grid -->
                <div class="row g-4">
                
                <?php foreach ($projects as $project): 
                    $voting_open = is_voting_open($project['approved_at']);
                    $days_remaining = voting_days_remaining($project['approved_at']);
                    $user_voted = in_array($project['id'], $user_votes);
                    $can_vote = is_logged_in() && $voting_open && (!$user_voted || $remaining_votes >= 0);
                    ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card project-card h-100 hover-shadow">
                            <div style="position: relative; overflow: hidden;">
                                <?php if ($project['image_path']): ?>
                                    <img src="/<?php echo e($project['image_path']); ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo e($project['title']); ?>"
                                         style="height: 220px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="/assets/images/placeholdderV.png" 
                                         class="card-img-top" 
                                         alt="Placeholder"
                                         style="height: 220px; object-fit: cover; opacity: 0.7;">
                                <?php endif; ?>
                                
                                <!-- Status Badge Overlay -->
                                <?php if ($voting_open): ?>
                                    <span class="badge bg-success position-absolute top-0 end-0 m-2">
                                        <i class="bi bi-clock-fill me-1"></i> <?php echo $days_remaining; ?> days left
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary position-absolute top-0 end-0 m-2">
                                        Voting closed
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <div class="mb-2">
                                    <span class="badge bg-<?php echo get_category_color($project['category_id']); ?> rounded-pill">
                                        <?php echo e($project['category_name']); ?>
                                    </span>
                                </div>
                                
                                <h5 class="card-title mb-3">
                                    <a href="/pages/project.php?id=<?php echo $project['id']; ?>" 
                                       class="text-decoration-none stretched-link">
                                        <?php echo e($project['title']); ?>
                                    </a>
                                </h5>
                                
                                <p class="card-text text-muted flex-grow-1" style="line-height: 1.6;">
                                    <?php echo e(truncate($project['description'], 120)); ?>
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                    <span class="text-primary fw-semibold">
                                        <i class="bi bi-hand-thumbs-up-fill me-1"></i>
                                        <span class="vote-count-<?php echo $project['id']; ?>">
                                            <?php echo $project['vote_count']; ?>
                                        </span> votes
                                    </span>
                                    
                                    <span class="text-muted small">
                                        <i class="bi bi-geo-alt-fill me-1"></i><?php echo e($project['postal_code']); ?>
                                    </span>
                                </div>
                                    
                                    <?php if (is_logged_in() && $voting_open): ?>
                                        <!-- EXTRA TASK: AJAX Voting (1.0 pt) -->
                                        <div class="mt-3 position-relative" style="z-index: 1;">
                                            <form method="POST" action="/actions/vote-action.php" class="vote-form">
                                                <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                                <input type="hidden" name="action" value="<?php echo $user_voted ? 'unvote' : 'vote'; ?>">
                                                
                                                <?php if ($user_voted): ?>
                                                    <button type="submit" class="btn btn-success w-100 btn-lg">
                                                        <i class="bi bi-check-circle-fill me-1"></i> Voted
                                                    </button>
                                                <?php elseif ($remaining_votes > 0): ?>
                                                    <button type="submit" class="btn btn-primary w-100 btn-lg">
                                                        <i class="bi bi-hand-thumbs-up-fill me-1"></i> Vote
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-outline-secondary w-100" disabled 
                                                            title="Maximum votes reached for this category">
                                                        <i class="bi bi-x-circle me-1"></i> No votes left
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        </div>
                                    <?php elseif (!is_logged_in() && $voting_open): ?>
                                        <div class="mt-3 position-relative" style="z-index: 1;">
                                            <a href="/pages/login.php" class="btn btn-outline-primary w-100 btn-lg">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Login to Vote
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
            <?php
        }
        
        if (!$has_projects) {
            echo '<div class="row"><div class="col-12 text-center py-5">';
            echo '<div class="text-muted">';
            echo '<h3>No projects found</h3>';
            echo '<p>Be the first to submit a project!</p>';
            if (is_logged_in()) {
                echo '<a href="/pages/submit-project.php" class="btn btn-primary mt-3">Submit Project</a>';
            } else {
                echo '<a href="/pages/register.php" class="btn btn-primary mt-3">Register to Submit</a>';
            }
            echo '</div>';
            echo '</div></div>';
        }
        
    } catch (PDOException $e) {
        echo '<div class="row"><div class="col-12">';
        echo '<div class="alert alert-danger" role="alert">';
        echo 'Error loading projects. Please try again later.';
        echo '</div>';
        echo '</div></div>';
        error_log("Homepage error: " . $e->getMessage());
    }
    ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

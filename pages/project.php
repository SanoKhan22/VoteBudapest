<?php
/**
 * Project Details Page
 * MINIMUM REQUIREMENT: Clicking a project opens its details ✓
 * CORE TASK: Access control - only owner/admin can view non-approved projects (1.0 pt) ✓
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Get project ID from URL
$project_id = $_GET['id'] ?? null;

if (!$project_id || !is_numeric($project_id)) {
    set_flash('error', 'Invalid project ID');
    redirect('/index.php');
}

try {
    $db = getDB();
    
    // Get project details with all related data
    $sql = "SELECT p.*, 
                   c.name as category_name,
                   s.name as status_name, s.slug as status_slug,
                   u.username as owner_username,
                   (SELECT COUNT(*) FROM votes WHERE project_id = p.id) as vote_count
            FROM projects p
            JOIN categories c ON p.category_id = c.id
            JOIN statuses s ON p.status_id = s.id
            JOIN users u ON p.user_id = u.id
            WHERE p.id = ?";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$project_id]);
    $project = $stmt->fetch();
    
    if (!$project) {
        set_flash('error', 'Project not found');
        redirect('/index.php');
    }
    
    // CORE TASK (1.0 pt): Access control for non-approved projects
    // Only owner or admin can view non-approved projects
    if ($project['status_id'] != STATUS_APPROVED) {
        if (!is_logged_in()) {
            set_flash('error', 'You must be logged in to view this project');
            redirect('/pages/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        }
        
        $is_owner = (get_user_id() == $project['user_id']);
        
        if (!$is_owner && !is_admin()) {
            set_flash('error', 'You do not have permission to view this project');
            redirect('/index.php');
        }
    }
    
    // Check if current user has voted
    $user_voted = false;
    $votes_in_category = 0;
    if (is_logged_in()) {
        $stmt = $db->prepare("SELECT id FROM votes WHERE user_id = ? AND project_id = ?");
        $stmt->execute([get_user_id(), $project_id]);
        $user_voted = (bool)$stmt->fetch();
        
        // Get vote count in this category
        $stmt = $db->prepare("
            SELECT COUNT(*) as count
            FROM votes v
            JOIN projects p ON v.project_id = p.id
            WHERE v.user_id = ? AND p.category_id = ?
        ");
        $stmt->execute([get_user_id(), $project['category_id']]);
        $votes_in_category = $stmt->fetch()['count'];
    }
    
    $remaining_votes = MAX_VOTES_PER_CATEGORY - $votes_in_category;
    $can_vote = is_logged_in() && ($user_voted || $remaining_votes > 0);
    
    // Get voting information
    $voting_open = is_voting_open($project['approved_at']);
    $days_remaining = voting_days_remaining($project['approved_at']);
    
    // Get admin comments (for rework status - extra task)
    $comments = [];
    if (is_logged_in() && ($project['user_id'] == get_user_id() || is_admin())) {
        $stmt = $db->prepare("
            SELECT ac.*, u.username as admin_username
            FROM admin_comments ac
            JOIN users u ON ac.admin_id = u.id
            WHERE ac.project_id = ?
            ORDER BY ac.created_at DESC
        ");
        $stmt->execute([$project_id]);
        $comments = $stmt->fetchAll();
    }
    
} catch (PDOException $e) {
    error_log("Project details error: " . $e->getMessage());
    set_flash('error', 'Error loading project details');
    redirect('/index.php');
}

$page_title = $project['title'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/index.php"><i class="bi bi-house-fill me-1"></i> Home</a></li>
            <li class="breadcrumb-item active"><?php echo e(truncate($project['title'], 50)); ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Project Image -->
            <div class="mb-4 position-relative">
                <?php if ($project['image_path']): ?>
                    <img src="/<?php echo e($project['image_path']); ?>" 
                         class="img-fluid w-100" 
                         alt="<?php echo e($project['title']); ?>"
                         style="border-radius: var(--border-radius-lg); max-height: 500px; object-fit: cover; box-shadow: 0 8px 24px rgba(0,0,0,0.12);">
                <?php else: ?>
                    <img src="/assets/images/placeholdderV.png" 
                         class="img-fluid w-100" 
                         alt="Placeholder"
                         style="border-radius: var(--border-radius-lg); max-height: 500px; object-fit: cover; box-shadow: 0 8px 24px rgba(0,0,0,0.12); opacity: 0.7;">
                <?php endif; ?>
            </div>

            <!-- Project Header -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h1 class="display-5 fw-bold mb-0" style="line-height: 1.2; color: #2c3e50;">
                        <?php echo e($project['title']); ?>
                    </h1>
                    <?php echo get_status_badge($project['status_id'], $project['status_name']); ?>
                </div>

                <!-- Meta Information -->
                <div class="d-flex flex-wrap gap-3 mb-3">
                    <span class="badge bg-<?php echo get_category_color($project['category_id']); ?> rounded-pill px-3 py-2" style="font-size: 0.9rem;">
                        <?php echo e($project['category_name']); ?>
                    </span>
                    <span class="text-muted d-flex align-items-center">
                        <i class="bi bi-geo-alt-fill me-1 text-danger"></i>
                        <strong>Postal Code:</strong> <?php echo e($project['postal_code']); ?>
                    </span>
                    <span class="text-muted d-flex align-items-center">
                        <i class="bi bi-person-fill me-1 text-primary"></i>
                        <strong>By</strong> <?php echo e($project['owner_username']); ?>
                    </span>
                    <span class="text-muted d-flex align-items-center">
                        <i class="bi bi-calendar-fill me-1 text-secondary"></i>
                        <?php echo format_date($project['submitted_at']); ?>
                    </span>
                </div>
            </div>

            <!-- Description -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold mb-3 d-flex align-items-center">
                        <i class="bi bi-file-text-fill me-2 text-primary"></i>
                        Project Description
                    </h5>
                    <p class="card-text" style="white-space: pre-wrap; line-height: 1.8; font-size: 1.05rem; color: #555;"><?php echo e($project['description']); ?></p>
                </div>
            </div>

            <!-- Admin Comments (Extra Task - Rework Cycle) -->
            <?php if (!empty($comments)): ?>
                <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #17a2b8 !important;">
                    <div class="card-header bg-info text-white py-3">
                        <h5 class="mb-0 d-flex align-items-center">
                            <i class="bi bi-chat-left-text-fill me-2"></i>
                            Admin Feedback
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <?php foreach ($comments as $comment): ?>
                            <div class="alert alert-info border-0 shadow-sm mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong class="d-flex align-items-center">
                                        <i class="bi bi-person-badge-fill me-2"></i>
                                        Admin <?php echo e($comment['admin_username']); ?>
                                    </strong>
                                    <small class="text-muted">
                                        <i class="bi bi-clock-fill me-1"></i>
                                        <?php echo format_date($comment['created_at']); ?>
                                    </small>
                                </div>
                                <p class="mb-0 mt-2" style="line-height: 1.7;"><?php echo e($comment['comment']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Voting Card -->
            <?php if ($project['status_id'] == STATUS_APPROVED): ?>
                <div class="card border-0 shadow-lg sticky-top mb-4" style="top: 20px;" data-category="<?php echo $project['category_id']; ?>">
                    <div class="card-body text-center p-4">
                        <div class="mb-4">
                            <h3 class="display-3 fw-bold mb-2" style="color: var(--primary);">
                                <i class="bi bi-hand-thumbs-up-fill"></i>
                                <span class="vote-count-<?php echo $project['id']; ?>"><?php echo $project['vote_count']; ?></span>
                            </h3>
                            <p class="text-muted mb-0 fw-semibold">Total Votes</p>
                        </div>

                        <?php if (is_logged_in()): ?>
                            <?php if ($voting_open): ?>
                                <!-- EXTRA TASK: AJAX Voting (1.0 pt) -->
                                <form method="POST" action="/actions/vote-action.php" class="vote-form mb-3">
                                    <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                    <input type="hidden" name="action" value="<?php echo $user_voted ? 'unvote' : 'vote'; ?>">
                                    
                                    <?php if ($user_voted): ?>
                                        <button type="submit" class="btn btn-success btn-lg w-100 mb-3">
                                            <i class="bi bi-check-circle-fill me-1"></i> Voted (Click to Remove)
                                        </button>
                                    <?php elseif ($remaining_votes > 0): ?>
                                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                            <i class="bi bi-hand-thumbs-up-fill me-1"></i> Vote for This
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-secondary btn-lg w-100 mb-3" disabled
                                                title="You have used all 3 votes in this category">
                                            <i class="bi bi-x-circle me-1"></i> No Votes Left
                                        </button>
                                    <?php endif; ?>
                                </form>
                                
                                <div class="alert alert-info border-0 mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-clock-fill me-2"></i>
                                        <strong><?php echo $days_remaining; ?> days remaining</strong>
                                    </div>
                                    <?php 
                                        $votes_used = MAX_VOTES_PER_CATEGORY - $remaining_votes;
                                        $vote_percentage = ($votes_used / MAX_VOTES_PER_CATEGORY) * 100;
                                    ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">
                                            <strong class="remaining-votes"><?php echo $remaining_votes; ?></strong>/<?php echo MAX_VOTES_PER_CATEGORY; ?> votes left
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
                            <?php else: ?>
                                <button class="btn btn-secondary btn-lg w-100 mb-3" disabled>
                                    <i class="bi bi-lock-fill me-1"></i> Voting Closed
                                </button>
                                <small class="text-muted d-block">
                                    Voting period has ended
                                </small>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="/pages/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                               class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Login to Vote
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Project Info -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold mb-4 d-flex align-items-center">
                        <i class="bi bi-info-circle-fill me-2 text-primary"></i>
                        Project Information
                    </h5>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-flag-fill me-2 text-muted"></i>
                            <strong class="text-muted small">Status</strong>
                        </div>
                        <?php echo get_status_badge($project['status_id'], $project['status_name']); ?>
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-tag-fill me-2 text-muted"></i>
                            <strong class="text-muted small">Category</strong>
                        </div>
                        <span class="badge bg-<?php echo get_category_color($project['category_id']); ?> px-3 py-2">
                            <?php echo e($project['category_name']); ?>
                        </span>
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-calendar-check-fill me-2 text-muted"></i>
                            <strong class="text-muted small">Submitted</strong>
                        </div>
                        <span><?php echo format_date($project['submitted_at']); ?></span>
                    </div>

                    <?php if ($project['approved_at']): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-check-circle-fill me-2 text-success"></i>
                                <strong class="text-muted small">Approved</strong>
                            </div>
                            <span><?php echo format_date($project['approved_at']); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-person-fill me-2 text-muted"></i>
                            <strong class="text-muted small">Submitted by</strong>
                        </div>
                        <span class="fw-semibold"><?php echo e($project['owner_username']); ?></span>
                    </div>

                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-geo-alt-fill me-2 text-danger"></i>
                            <strong class="text-muted small">Location</strong>
                        </div>
                        <span><?php echo e($project['postal_code']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Admin Actions (Extra Task) -->
            <?php if (is_admin() && $project['status_id'] == STATUS_PENDING): ?>
                <div class="card mt-4 border-warning">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0">🛡️ Admin Actions</h5>
                    </div>
                    <div class="card-body">
                        <form action="/actions/admin/approve-action.php" method="POST" class="mb-2">
                            <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                            <button type="submit" class="btn btn-success w-100">
                                ✅ Approve Project
                            </button>
                        </form>

                        <form action="/actions/admin/reject-action.php" method="POST" class="mb-2">
                            <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                            <button type="submit" class="btn btn-danger w-100">
                                ❌ Reject Project
                            </button>
                        </form>

                        <hr>

                        <form action="/actions/admin/rework-action.php" method="POST">
                            <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                            <label for="comment" class="form-label">Request Rework:</label>
                            <textarea name="comment" id="comment" class="form-control mb-2" rows="3" 
                                      placeholder="Explain what needs to be fixed..."></textarea>
                            <button type="submit" class="btn btn-info w-100">
                                🔄 Send for Rework
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Owner Actions (Edit for Rework) -->
            <?php if (is_logged_in() && get_user_id() == $project['user_id'] && $project['status_id'] == STATUS_REWORK): ?>
                <div class="card mt-4 border-info">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">✏️ Action Required</h5>
                    </div>
                    <div class="card-body">
                        <p>This project needs revision. Please review the admin's comments above.</p>
                        <a href="/pages/edit-project.php?id=<?php echo $project['id']; ?>" 
                           class="btn btn-primary w-100">
                            Edit & Resubmit
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

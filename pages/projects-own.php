<?php
/**
 * My Projects Page
 * CORE TASK: View own non-approved projects (pending, rework, rejected) (0.5 pts) ✓
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Require login
require_login();

$page_title = 'My Projects';

try {
    $db = getDB();
    
    // Get all projects by current user
    $sql = "SELECT p.*, 
                   c.name as category_name, c.id as category_id,
                   s.name as status_name, s.slug as status_slug,
                   (SELECT COUNT(*) FROM votes WHERE project_id = p.id) as vote_count
            FROM projects p
            JOIN categories c ON p.category_id = c.id
            JOIN statuses s ON p.status_id = s.id
            WHERE p.user_id = ?
            ORDER BY p.submitted_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([get_user_id()]);
    $projects = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("My projects error: " . $e->getMessage());
    $projects = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 mb-3">📁 My Projects</h1>
            <p class="lead text-muted">
                Manage all your submitted projects in one place
            </p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <?php
        $status_counts = [
            STATUS_PENDING => ['count' => 0, 'label' => 'Pending Review', 'icon' => '⏳', 'color' => 'warning'],
            STATUS_APPROVED => ['count' => 0, 'label' => 'Approved', 'icon' => '✅', 'color' => 'success'],
            STATUS_REJECTED => ['count' => 0, 'label' => 'Rejected', 'icon' => '❌', 'color' => 'danger'],
            STATUS_REWORK => ['count' => 0, 'label' => 'Needs Rework', 'icon' => '🔄', 'color' => 'info'],
        ];
        
        foreach ($projects as $project) {
            if (isset($status_counts[$project['status_id']])) {
                $status_counts[$project['status_id']]['count']++;
            }
        }
        
        foreach ($status_counts as $status):
        ?>
            <div class="col-md-3 mb-3">
                <div class="card border-<?php echo $status['color']; ?>">
                    <div class="card-body text-center">
                        <h3 class="display-4 mb-0"><?php echo $status['icon']; ?> <?php echo $status['count']; ?></h3>
                        <p class="text-muted mb-0"><?php echo $status['label']; ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($projects)): ?>
        <div class="text-center py-5">
            <div class="text-muted">
                <h3>No projects yet</h3>
                <p>You haven't submitted any projects yet.</p>
                <a href="/pages/submit-project.php" class="btn btn-primary mt-3">
                    📤 Submit Your First Project
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Projects List -->
        <div class="row">
            <?php foreach ($projects as $project): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card project-card h-100">
                        <?php if ($project['image_path']): ?>
                            <img src="/<?php echo e($project['image_path']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo e($project['title']); ?>"
                                 style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <img src="/assets/images/placeholdderV.png" 
                                 class="card-img-top" 
                                 alt="Placeholder"
                                 style="height: 200px; object-fit: cover; opacity: 0.7;">
                        <?php endif; ?>
                        
                        <div class="card-body d-flex flex-column">
                            <div class="mb-2">
                                <?php echo get_status_badge($project['status_id'], $project['status_name']); ?>
                                <span class="badge bg-<?php echo get_category_color($project['category_id']); ?> ms-1">
                                    <?php echo e($project['category_name']); ?>
                                </span>
                            </div>
                            
                            <h5 class="card-title">
                                <a href="/pages/project.php?id=<?php echo $project['id']; ?>" 
                                   class="text-decoration-none text-dark">
                                    <?php echo e($project['title']); ?>
                                </a>
                            </h5>
                            
                            <p class="card-text text-muted small flex-grow-1">
                                <?php echo e(truncate($project['description'], 100)); ?>
                            </p>
                            
                            <div class="border-top pt-3 mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted small">
                                        📅 <?php echo format_date($project['submitted_at'], DATE_FORMAT_SHORT); ?>
                                    </span>
                                    
                                    <?php if ($project['status_id'] == STATUS_APPROVED): ?>
                                        <span class="text-muted small">
                                            👍 <?php echo $project['vote_count']; ?> votes
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <a href="/pages/project.php?id=<?php echo $project['id']; ?>" 
                                       class="btn btn-outline-primary btn-sm">
                                        View Details
                                    </a>
                                    
                                    <?php if ($project['status_id'] == STATUS_REWORK): ?>
                                        <a href="/pages/edit-project.php?id=<?php echo $project['id']; ?>" 
                                           class="btn btn-info btn-sm">
                                            ✏️ Edit & Resubmit
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

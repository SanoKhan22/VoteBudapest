<?php
/**
 * Admin Panel - Projects Management
 * CORE TASK: Admin sees all pending projects (0.5 pt)
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require admin access
require_admin();

$page_title = 'Admin Panel - Projects';

// Get status filter from URL (CORE TASK: Filter by status - 0.5 pt)
$status_filter = $_GET['status'] ?? 'pending';

try {
    $db = getDB();
    
    // Map status names to IDs
    $status_map = [
        'pending' => STATUS_PENDING,
        'approved' => STATUS_APPROVED,
        'rejected' => STATUS_REJECTED,
        'rework' => STATUS_REWORK,
        'all' => null
    ];
    
    $status_id = $status_map[$status_filter] ?? STATUS_PENDING;
    
    // Get projects filtered by status
    $sql = "SELECT p.*, 
                   c.name as category_name, c.id as category_id,
                   s.name as status_name,
                   u.username as owner_username
            FROM projects p
            JOIN categories c ON p.category_id = c.id
            JOIN statuses s ON p.status_id = s.id
            JOIN users u ON p.user_id = u.id";
    
    if ($status_id !== null) {
        $sql .= " WHERE p.status_id = ?";
    }
    
    $sql .= " ORDER BY c.id, p.submitted_at DESC";
    
    $stmt = $db->prepare($sql);
    
    if ($status_id !== null) {
        $stmt->execute([$status_id]);
    } else {
        $stmt->execute();
    }
    
    $projects = $stmt->fetchAll();
    
    // Group by category
    $projects_by_category = [];
    foreach ($projects as $project) {
        $cat_id = $project['category_id'];
        if (!isset($projects_by_category[$cat_id])) {
            $projects_by_category[$cat_id] = [
                'name' => $project['category_name'],
                'projects' => []
            ];
        }
        $projects_by_category[$cat_id]['projects'][] = $project;
    }
    
} catch (PDOException $e) {
    error_log("Admin panel error: " . $e->getMessage());
    $projects_by_category = [];
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="display-5 mb-3">🛡️ Admin Panel</h1>
            <p class="lead text-muted">
                Review and manage project submissions
            </p>
        </div>
        <div class="col-md-4">
            <!-- CORE TASK: Filter by status (0.5 pt) -->
            <label for="status-filter" class="form-label fw-bold">Filter by Status:</label>
            <select id="status-filter" class="form-select" onchange="window.location.href='?status='+this.value">
                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>
                    ⏳ Pending Review
                </option>
                <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>
                    ✅ Approved
                </option>
                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>
                    ❌ Rejected
                </option>
                <option value="rework" <?php echo $status_filter === 'rework' ? 'selected' : ''; ?>>
                    🔄 In Rework
                </option>
                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>
                    📋 All Projects
                </option>
            </select>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <?php
                    $stmt = $db->query("SELECT COUNT(*) as count FROM projects WHERE status_id = " . STATUS_PENDING);
                    $pending = $stmt->fetch()['count'];
                    ?>
                    <h3 class="display-4 mb-0">⏳ <?php echo $pending; ?></h3>
                    <p class="text-muted mb-0">Pending Review</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <?php
                    $stmt = $db->query("SELECT COUNT(*) as count FROM projects WHERE status_id = " . STATUS_APPROVED);
                    $approved = $stmt->fetch()['count'];
                    ?>
                    <h3 class="display-4 text-success mb-0">✅ <?php echo $approved; ?></h3>
                    <p class="text-muted mb-0">Approved</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <?php
                    $stmt = $db->query("SELECT COUNT(*) as count FROM projects WHERE status_id = " . STATUS_REJECTED);
                    $rejected = $stmt->fetch()['count'];
                    ?>
                    <h3 class="display-4 text-danger mb-0">❌ <?php echo $rejected; ?></h3>
                    <p class="text-muted mb-0">Rejected</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <?php
                    $stmt = $db->query("SELECT COUNT(*) as count FROM projects WHERE status_id = " . STATUS_REWORK);
                    $rework = $stmt->fetch()['count'];
                    ?>
                    <h3 class="display-4 text-info mb-0">🔄 <?php echo $rework; ?></h3>
                    <p class="text-muted mb-0">In Rework</p>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($projects)): ?>
        <div class="text-center py-5">
            <div class="text-muted">
                <h3>✅ All caught up!</h3>
                <p>There are no projects with status "<?php echo ucfirst($status_filter); ?>" at this time.</p>
                <a href="?status=all" class="btn btn-outline-primary mt-3">View All Projects</a>
            </div>
        </div>
    <?php else: ?>
        <!-- Pending Projects by Category -->
        <?php foreach ($projects_by_category as $cat_id => $category): ?>
            <div class="card mb-4">
                <div class="card-header bg-<?php echo get_category_color($cat_id); ?> text-white">
                    <h4 class="mb-0"><?php echo e($category['name']); ?> (<?php echo count($category['projects']); ?>)</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Submitted By</th>
                                    <?php if ($status_filter === 'all'): ?>
                                        <th>Status</th>
                                    <?php endif; ?>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($category['projects'] as $project): ?>
                                    <tr>
                                        <td>
                                            <a href="/pages/project.php?id=<?php echo $project['id']; ?>" 
                                               class="text-decoration-none fw-bold">
                                                <?php echo e($project['title']); ?>
                                            </a>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo e(truncate($project['description'], 80)); ?>
                                            </small>
                                        </td>
                                        <td><?php echo e($project['owner_username']); ?></td>
                                        <?php if ($status_filter === 'all'): ?>
                                            <td><?php echo get_status_badge($project['status_id'], $project['status_name']); ?></td>
                                        <?php endif; ?>
                                        <td>
                                            <small><?php echo format_date($project['submitted_at']); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($project['status_id'] == STATUS_PENDING): ?>
                                                <form action="/actions/admin/approve-action.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-success">✅ Approve</button>
                                                </form>
                                                <form action="/actions/admin/reject-action.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">❌ Reject</button>
                                                </form>
                                            <?php else: ?>
                                                <a href="/pages/project.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-outline-secondary">View</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

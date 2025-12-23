<?php
/**
 * Edit Project Page
 * EXTRA TASK: Edit and resubmit projects sent back for rework (2.0 pts)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Require login
require_login();

$project_id = $_GET['id'] ?? null;

if (!$project_id || !is_numeric($project_id)) {
    set_flash('error', 'Invalid project ID');
    redirect('/pages/projects-own.php');
}

try {
    $db = getDB();
    
    // Get project details
    $stmt = $db->prepare("
        SELECT p.*, c.name as category_name, s.name as status_name
        FROM projects p
        JOIN categories c ON p.category_id = c.id
        JOIN statuses s ON p.status_id = s.id
        WHERE p.id = ?
    ");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch();
    
    if (!$project) {
        set_flash('error', 'Project not found');
        redirect('/pages/projects-own.php');
    }
    
    // Check if user is the owner
    if ($project['user_id'] != get_user_id()) {
        set_flash('error', 'You do not have permission to edit this project');
        redirect('/pages/projects-own.php');
    }
    
    // Check if project status is REWORK
    if ($project['status_id'] != STATUS_REWORK) {
        set_flash('error', 'Only projects sent back for rework can be edited');
        redirect('/pages/project.php?id=' . $project_id);
    }
    
    // Get all categories
    $stmt = $db->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
    
    // Get admin comments
    $stmt = $db->prepare("
        SELECT ac.*, u.username as admin_username
        FROM admin_comments ac
        JOIN users u ON ac.admin_id = u.id
        WHERE ac.project_id = ?
        ORDER BY ac.created_at DESC
    ");
    $stmt->execute([$project_id]);
    $comments = $stmt->fetchAll();
    
    $page_title = 'Edit Project';
    require_once __DIR__ . '/../includes/header.php';
    
} catch (PDOException $e) {
    error_log("Edit project error: " . $e->getMessage());
    set_flash('error', 'Database error');
    redirect('/pages/projects-own.php');
}
?>

<!-- Hero Section -->
<div class="hero-section mb-5 py-5" style="background: linear-gradient(135deg, #477050 0%, #C8102E 100%);">
    <div class="container text-center text-white">
        <h1 class="display-4 fw-bold mb-3">
            <i class="bi bi-pencil-square me-2"></i> Revise Your Project
        </h1>
        <p class="lead mb-0" style="max-width: 700px; margin: 0 auto; opacity: 0.95;">
            Address the admin feedback below and resubmit your project for review
        </p>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">
            
            <!-- Admin Feedback Section -->
            <?php if (!empty($comments)): ?>
                <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #ffc107 !important;">
                    <div class="card-body p-4">
                        <h5 class="mb-3">
                            <i class="bi bi-megaphone-fill text-warning me-2"></i> Admin Feedback
                        </h5>
                        <?php foreach ($comments as $index => $comment): ?>
                            <div class="<?php echo $index > 0 ? 'mt-3 pt-3 border-top' : ''; ?>">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <strong class="text-primary">
                                        <i class="bi bi-person-badge me-1"></i> <?php echo e($comment['admin_username']); ?>
                                    </strong>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <?php echo date('M j, Y \a\t g:i A', strtotime($comment['created_at'])); ?>
                                    </small>
                                </div>
                                <div class="alert alert-warning mb-0" style="background-color: #fff4e5; border: none;">
                                    <?php echo nl2br(e($comment['comment'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Main Edit Form -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <form method="POST" action="/actions/edit-project-action.php" enctype="multipart/form-data">
                        <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                        
                        <h5 class="mb-4 pb-3 border-bottom">
                            <i class="bi bi-file-text text-primary me-2"></i> Basic Information
                        </h5>
                        
                        <!-- Title -->
                        <div class="mb-4">
                            <label for="title" class="form-label fw-semibold">
                                Project Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   id="title" 
                                   name="title" 
                                   value="<?php echo e($project['title']); ?>"
                                   minlength="10" 
                                   required>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i> Minimum 10 characters
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold">
                                Description <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control form-control-lg" 
                                      id="description" 
                                      name="description" 
                                      rows="8" 
                                      minlength="150" 
                                      required><?php echo e($project['description']); ?></textarea>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i> Explain your project clearly
                                </div>
                                <div class="form-text">
                                    <span id="char-count" class="fw-bold">0</span> / 150 characters minimum
                                </div>
                            </div>
                        </div>

                        <h5 class="mb-4 pb-3 border-bottom mt-5">
                            <i class="bi bi-geo-alt text-primary me-2"></i> Location & Category
                        </h5>

                        <!-- Category -->
                        <div class="mb-4">
                            <label for="category_id" class="form-label fw-semibold">
                                Category <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-lg" id="category_id" name="category_id" required>
                                <option value="">Choose category...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" 
                                            <?php echo $cat['id'] == $project['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo e($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i> Choose the best fitting category
                            </div>
                        </div>

                        <!-- Postal Code -->
                        <div class="mb-4">
                            <label for="postal_code" class="form-label fw-semibold">
                                Postal Code <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   id="postal_code" 
                                   name="postal_code" 
                                   value="<?php echo e($project['postal_code']); ?>"
                                   pattern="1\d{3}" 
                                   placeholder="e.g., 1051"
                                   required>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i> 4-digit Budapest postal code (1XXX format)
                            </div>
                        </div>

                        <h5 class="mb-4 pb-3 border-bottom mt-5">
                            <i class="bi bi-image text-primary me-2"></i> Visual
                        </h5>

                        <!-- Current Image -->
                        <?php if ($project['image_path']): ?>
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Current Image</label>
                                <div class="position-relative">
                                    <img src="/<?php echo e($project['image_path']); ?>" 
                                         alt="Current project image" 
                                         class="img-fluid rounded shadow-sm" 
                                         style="max-height: 300px; width: auto;">
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- New Image Upload (Optional) -->
                        <div class="mb-5">
                            <label for="image" class="form-label fw-semibold">
                                <?php echo $project['image_path'] ? 'Replace Image (Optional)' : 'Upload Image'; ?>
                            </label>
                            <input type="file" 
                                   class="form-control form-control-lg" 
                                   id="image" 
                                   name="image" 
                                   accept="image/jpeg,image/png,image/gif,image/webp">
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i> JPG, PNG, GIF, or WebP • Maximum 5MB
                                <?php if ($project['image_path']): ?>
                                    <br><small class="text-muted">Leave empty to keep current image</small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-grid gap-3">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm" style="padding: 14px 0;">
                                <i class="bi bi-arrow-repeat me-2"></i> Resubmit for Review
                            </button>
                            <a href="/pages/project.php?id=<?php echo $project['id']; ?>" 
                               class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-x-circle me-2"></i> Cancel
                            </a>
                        </div>

                        <div class="alert alert-info border-0 mt-4" style="background-color: #e7f3ff;">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-info-circle-fill text-info me-3" style="font-size: 1.5rem;"></i>
                                <div>
                                    <small>
                                        After resubmission, your project will be sent back to administrators for review with status changed to "Pending".
                                    </small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Character counter for description
const descriptionTextarea = document.getElementById('description');
const charCount = document.getElementById('char-count');

function updateCharCount() {
    const count = descriptionTextarea.value.length;
    charCount.textContent = count;
    charCount.parentElement.classList.toggle('text-danger', count < 150);
    charCount.parentElement.classList.toggle('text-success', count >= 150);
}

descriptionTextarea.addEventListener('input', updateCharCount);
updateCharCount(); // Initial count
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
/**
 * Submit Project Page
 * MINIMUM REQUIREMENTS:
 * - User can submit new project ✓
 * - Project title minimum 10 characters ✓
 * - Project description minimum 150 characters ✓
 * - ID, owner, submission date set automatically ✓
 * 
 * CORE TASKS:
 * - Category selectable from fixed list (0.5 pts) ✓
 * - Postal code validation (1.0 pts) ✓
 * - Image upload optional but valid (0.5 pts) ✓
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Require login
require_login();

$page_title = 'Submit New Project';

// Get categories for dropdown
try {
    $db = getDB();
    $stmt = $db->query("SELECT id, name FROM categories ORDER BY id");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<div class="hero-section mb-5 py-5" style="background: linear-gradient(135deg, #C8102E 0%, #477050 100%);">
    <div class="container text-center text-white">
        <h1 class="display-4 fw-bold mb-3">
            <i class="bi bi-lightbulb-fill me-2"></i> Submit Your Project Idea
        </h1>
        <p class="lead mb-0" style="max-width: 700px; margin: 0 auto; opacity: 0.95;">
            Have a brilliant idea to improve Budapest? Share it with the community and let residents vote on what matters most.
        </p>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">
            
            <!-- Progress Indicator -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                                 style="width: 50px; height: 50px; font-size: 1.25rem;">
                                <i class="bi bi-pencil-fill"></i>
                            </div>
                            <p class="mt-2 mb-0 small fw-semibold text-primary">Fill Details</p>
                        </div>
                        <div style="flex: 1; height: 2px; background: #e0e0e0; margin: 0 10px;"></div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center" 
                                 style="width: 50px; height: 50px; font-size: 1.25rem; border: 2px dashed #ccc;">
                                <i class="bi bi-check2-circle"></i>
                            </div>
                            <p class="mt-2 mb-0 small text-muted">Admin Review</p>
                        </div>
                        <div style="flex: 1; height: 2px; background: #e0e0e0; margin: 0 10px;"></div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center" 
                                 style="width: 50px; height: 50px; font-size: 1.25rem; border: 2px dashed #ccc;">
                                <i class="bi bi-hand-thumbs-up-fill"></i>
                            </div>
                            <p class="mt-2 mb-0 small text-muted">Public Voting</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Form -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    
                    <div class="alert alert-info border-0 mb-4" style="background-color: #e7f3ff;">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-info-circle-fill text-info me-3" style="font-size: 1.5rem;"></i>
                            <div>
                                <h6 class="mb-2 fw-bold">What happens next?</h6>
                                <p class="mb-0 small">Your project will be reviewed by administrators. Once approved, it will be published for community voting for 2 weeks. Make sure to provide clear, detailed information to help your idea succeed!</p>
                            </div>
                        </div>
                    </div>

                    <form action="/actions/submit-project-action.php" method="POST" enctype="multipart/form-data" novalidate>
                        
                        <h5 class="mb-4 pb-3 border-bottom">
                            <i class="bi bi-file-text text-primary me-2"></i> Basic Information
                        </h5>
                        
                        <!-- Title -->
                        <div class="mb-4">
                            <label for="title" class="form-label fw-semibold">
                                Project Title <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control form-control-lg" 
                                id="title" 
                                name="title" 
                                required
                                maxlength="255"
                                placeholder="e.g., New Playground in City Park"
                                value="<?php echo e($_POST['title'] ?? ''); ?>"
                            >
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i> Minimum 10 characters. Make it clear and descriptive.
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold">
                                Project Description <span class="text-danger">*</span>
                            </label>
                            <textarea 
                                class="form-control form-control-lg" 
                                id="description" 
                                name="description" 
                                rows="8" 
                                required
                                placeholder="Describe your project in detail: what it is, why it's needed, who will benefit, and how it will improve our community..."
                            ><?php echo e($_POST['description'] ?? ''); ?></textarea>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i> Explain your vision clearly to get community support
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
                                <option value="">-- Select a category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"
                                            <?php echo (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo e($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i> Choose the category that best fits your project
                            </div>
                        </div>

                        <!-- Postal Code -->
                        <div class="mb-4">
                            <label for="postal_code" class="form-label fw-semibold">
                                Postal Code <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control form-control-lg" 
                                id="postal_code" 
                                name="postal_code" 
                                required
                                maxlength="4"
                                pattern="\d{4}"
                                placeholder="e.g., 1051"
                                value="<?php echo e($_POST['postal_code'] ?? ''); ?>"
                            >
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i> 4-digit Budapest postal code (1XXX format)
                                <br>
                                <small class="text-muted">
                                    <strong>Examples:</strong> 1051 (District V), 1082 (District VIII), 1117 (District XI), 1007 (Margaret Island)
                                </small>
                            </div>
                        </div>

                        <h5 class="mb-4 pb-3 border-bottom mt-5">
                            <i class="bi bi-image text-primary me-2"></i> Visual (Optional)
                        </h5>

                        <!-- Image Upload -->
                        <div class="mb-5">
                            <label for="image" class="form-label fw-semibold">
                                Project Image <span class="text-muted">(Optional)</span>
                            </label>
                            <input 
                                type="file" 
                                class="form-control form-control-lg" 
                                id="image" 
                                name="image"
                                accept="image/jpeg,image/png,image/gif,image/webp"
                            >
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i> Upload an image to illustrate your project
                                <br>
                                <small class="text-muted">Accepted: JPG, PNG, GIF, WebP • Max size: 5MB</small>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-3">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm" style="padding: 14px 0;">
                                <i class="bi bi-send-fill me-2"></i> Submit Project for Review
                            </button>
                            <a href="/index.php" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-x-circle me-2"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Character counter for description
document.getElementById('description').addEventListener('input', function() {
    const count = this.value.length;
    const counter = document.getElementById('char-count');
    counter.textContent = count;
    
    if (count < 150) {
        counter.className = 'text-danger fw-bold';
    } else {
        counter.className = 'text-success fw-bold';
    }
});

// Trigger on page load
document.getElementById('description').dispatchEvent(new Event('input'));
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

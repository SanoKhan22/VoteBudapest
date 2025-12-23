<?php
/**
 * Admin Statistics Page
 * CORE TASK: Admin sees most voted project (0.5 pt)
 * CORE TASK: Admin sees top 3 per category (0.5 pt)
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require admin access
require_admin();

$page_title = 'Statistics';

try {
    $db = getDB();
    
    // Get most voted project (single item)
    $stmt = $db->query("
        SELECT p.*, c.name as category_name,
               (SELECT COUNT(*) FROM votes WHERE project_id = p.id) as vote_count
        FROM projects p
        JOIN categories c ON p.category_id = c.id
        WHERE p.status_id = " . STATUS_APPROVED . "
        ORDER BY vote_count DESC
        LIMIT 1
    ");
    $most_voted = $stmt->fetch();
    
    // Get top 3 per category
    $stmt = $db->query("SELECT id, name FROM categories ORDER BY id");
    $categories = $stmt->fetchAll();
    
    $top_by_category = [];
    foreach ($categories as $cat) {
        $stmt = $db->prepare("
            SELECT p.*, 
                   (SELECT COUNT(*) FROM votes WHERE project_id = p.id) as vote_count
            FROM projects p
            WHERE p.category_id = ? AND p.status_id = ?
            ORDER BY vote_count DESC
            LIMIT 3
        ");
        $stmt->execute([$cat['id'], STATUS_APPROVED]);
        $top_by_category[$cat['id']] = [
            'name' => $cat['name'],
            'projects' => $stmt->fetchAll()
        ];
    }
    
    // General statistics
    $stats = [];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM projects");
    $stats['total_projects'] = $stmt->fetch()['count'];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM projects WHERE status_id = " . STATUS_APPROVED);
    $stats['approved_projects'] = $stmt->fetch()['count'];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM votes");
    $stats['total_votes'] = $stmt->fetch()['count'];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE is_admin = FALSE");
    $stats['total_users'] = $stmt->fetch()['count'];
    
    // EXTRA TASK: Data for charts (2.0 pts)
    
    // Votes per category
    $stmt = $db->query("
        SELECT c.name, COUNT(v.id) as vote_count
        FROM categories c
        LEFT JOIN projects p ON c.id = p.category_id AND p.status_id = " . STATUS_APPROVED . "
        LEFT JOIN votes v ON p.id = v.project_id
        GROUP BY c.id, c.name
        ORDER BY c.id
    ");
    $votes_by_category = $stmt->fetchAll();
    
    // Project status distribution
    $stmt = $db->query("
        SELECT s.name, COUNT(p.id) as count
        FROM statuses s
        LEFT JOIN projects p ON s.id = p.status_id
        GROUP BY s.id, s.name
        ORDER BY s.id
    ");
    $projects_by_status = $stmt->fetchAll();
    
    // Projects by category
    $stmt = $db->query("
        SELECT c.name, COUNT(p.id) as count
        FROM categories c
        LEFT JOIN projects p ON c.id = p.category_id
        GROUP BY c.id, c.name
        ORDER BY c.id
    ");
    $projects_by_category = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Statistics error: " . $e->getMessage());
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Hero Section -->
<div class="hero-section mb-5 py-5" style="background: linear-gradient(135deg, #C8102E 0%, #477050 100%);">
    <div class="container text-center text-white">
        <h1 class="display-4 fw-bold mb-3">
            <i class="bi bi-graph-up-arrow me-2"></i> Platform Statistics
        </h1>
        <p class="lead mb-0" style="max-width: 700px; margin: 0 auto; opacity: 0.95;">
            Comprehensive overview of community engagement, voting patterns, and project analytics
        </p>
    </div>
</div>

<div class="container">

    <!-- General Statistics -->
    <div class="row mb-5">
        <div class="col-md-3 mb-3">
            <div class="card text-center border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);">
                <div class="card-body p-4">
                    <div class="mb-2">
                        <i class="bi bi-folder-fill" style="font-size: 2.5rem; color: #C8102E;"></i>
                    </div>
                    <h2 class="display-5 fw-bold mb-1" style="color: #C8102E;"><?php echo $stats['total_projects']; ?></h2>
                    <p class="text-muted mb-0 fw-semibold">Total Projects</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);">
                <div class="card-body p-4">
                    <div class="mb-2">
                        <i class="bi bi-check-circle-fill" style="font-size: 2.5rem; color: #28a745;"></i>
                    </div>
                    <h2 class="display-5 fw-bold mb-1" style="color: #28a745;"><?php echo $stats['approved_projects']; ?></h2>
                    <p class="text-muted mb-0 fw-semibold">Approved</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);">
                <div class="card-body p-4">
                    <div class="mb-2">
                        <i class="bi bi-hand-thumbs-up-fill" style="font-size: 2.5rem; color: #477050;"></i>
                    </div>
                    <h2 class="display-5 fw-bold mb-1" style="color: #477050;"><?php echo $stats['total_votes']; ?></h2>
                    <p class="text-muted mb-0 fw-semibold">Total Votes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);">
                <div class="card-body p-4">
                    <div class="mb-2">
                        <i class="bi bi-people-fill" style="font-size: 2.5rem; color: #17a2b8;"></i>
                    </div>
                    <h2 class="display-5 fw-bold mb-1" style="color: #17a2b8;"><?php echo $stats['total_users']; ?></h2>
                    <p class="text-muted mb-0 fw-semibold">Community Members</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CORE TASK: Most Voted Project (0.5 pt) -->
    <?php if ($most_voted): ?>
        <div class="card mb-5 border-0 shadow-sm" style="border-left: 5px solid #ffc107 !important;">
            <div class="card-body p-4">
                <h4 class="mb-4">
                    <i class="bi bi-trophy-fill me-2" style="color: #ffc107;"></i>
                    <span class="fw-bold">Most Voted Project</span>
                </h4>
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-3">
                            <a href="/pages/project.php?id=<?php echo $most_voted['id']; ?>" 
                               class="text-decoration-none text-dark">
                                <?php echo e($most_voted['title']); ?>
                            </a>
                        </h3>
                        <p class="text-muted mb-3" style="line-height: 1.7;">
                            <?php echo e(truncate($most_voted['description'], 200)); ?>
                        </p>
                        <span class="badge px-3 py-2" style="background-color: <?php echo get_category_color($most_voted['category_id']) === 'primary' ? '#C8102E' : '#477050'; ?>; color: white; font-size: 0.9rem;">
                            <i class="bi bi-tag-fill me-1"></i> <?php echo e($most_voted['category_name']); ?>
                        </span>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="p-4">
                            <i class="bi bi-hand-thumbs-up-fill mb-3" style="font-size: 4rem; color: #ffc107;"></i>
                            <h2 class="display-3 fw-bold mb-2" style="color: #ffc107;"><?php echo $most_voted['vote_count']; ?></h2>
                            <p class="text-muted fw-semibold mb-0">Total Votes</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- EXTRA TASK: Visual Charts (2.0 pts) -->
    <h3 class="mb-4 fw-bold">
        <i class="bi bi-bar-chart-fill me-2" style="color: #C8102E;"></i> Visual Analytics
    </h3>
    <div class="row mb-5">
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-bar-chart-line me-2" style="color: #C8102E;"></i>
                        Votes by Category
                    </h5>
                </div>
                <div class="card-body p-4">
                    <canvas id="votesChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-pie-chart-fill me-2" style="color: #477050;"></i>
                        Project Status
                    </h5>
                </div>
                <div class="card-body p-4">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-graph-up me-2" style="color: #17a2b8;"></i>
                        Projects by Category
                    </h5>
                </div>
                <div class="card-body p-4">
                    <canvas id="categoryChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- CORE TASK: Top 3 Per Category (0.5 pt) -->
    <h3 class="mb-4 fw-bold">
        <i class="bi bi-star-fill me-2" style="color: #ffc107;"></i> Top 3 Projects by Category
    </h3>
    <div class="row">
        <?php foreach ($top_by_category as $cat_id => $category): ?>
            <div class="col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header text-white py-3" 
                         style="background: <?php echo get_category_color($cat_id) === 'primary' ? '#C8102E' : '#477050'; ?>;">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-trophy me-2"></i><?php echo e($category['name']); ?>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if (empty($category['projects'])): ?>
                            <p class="text-muted mb-0">
                                <i class="bi bi-info-circle me-2"></i>No approved projects in this category yet.
                            </p>
                        <?php else: ?>
                            <ol class="list-group list-group-numbered">
                                <?php foreach ($category['projects'] as $project): ?>
                                    <li class="list-group-item border-0 d-flex justify-content-between align-items-start px-0 py-3">
                                        <div class="ms-2 me-auto">
                                            <a href="/pages/project.php?id=<?php echo $project['id']; ?>" 
                                               class="fw-bold text-decoration-none">
                                                <?php echo e(truncate($project['title'], 50)); ?>
                                            </a>
                                        </div>
                                        <span class="badge rounded-pill px-3 py-2" 
                                              style="background-color: <?php echo get_category_color($cat_id) === 'primary' ? '#C8102E' : '#477050'; ?>; font-size: 0.9rem;">
                                            <i class="bi bi-hand-thumbs-up-fill me-1"></i> <?php echo $project['vote_count']; ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Budapest theme colors
const colors = {
    primary: '#C8102E',
    secondary: '#477050',
    info: '#17a2b8',
    warning: '#ffc107',
    success: '#28a745',
    danger: '#dc3545',
    pending: '#ffc107',
    approved: '#28a745',
    rejected: '#dc3545',
    rework: '#17a2b8'
};

// Chart configuration defaults
Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
Chart.defaults.color = '#6c757d';

// EXTRA TASK: Chart 1 - Votes Distribution by Category (Bar Chart)
const votesCtx = document.getElementById('votesChart').getContext('2d');
new Chart(votesCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($votes_by_category, 'name')); ?>,
        datasets: [{
            label: 'Total Votes',
            data: <?php echo json_encode(array_column($votes_by_category, 'vote_count')); ?>,
            backgroundColor: 'rgba(200, 16, 46, 0.8)',
            borderColor: colors.primary,
            borderWidth: 2,
            borderRadius: 6,
            hoverBackgroundColor: colors.primary
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                borderRadius: 6,
                titleFont: {
                    size: 14,
                    weight: 'bold'
                },
                bodyFont: {
                    size: 13
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1,
                    font: {
                        size: 12
                    }
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                ticks: {
                    font: {
                        size: 11
                    }
                },
                grid: {
                    display: false
                }
            }
        }
    }
});

// EXTRA TASK: Chart 2 - Project Status Distribution (Pie Chart)
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode(array_column($projects_by_status, 'name')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($projects_by_status, 'count')); ?>,
            backgroundColor: [
                'rgba(255, 193, 7, 0.8)',
                'rgba(40, 167, 69, 0.8)',
                'rgba(220, 53, 69, 0.8)',
                'rgba(23, 162, 184, 0.8)'
            ],
            borderWidth: 3,
            borderColor: '#fff',
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    font: {
                        size: 13
                    },
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                borderRadius: 6,
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return `${label}: ${value} (${percentage}%)`;
                    }
                }
            }
        }
    }
});

// EXTRA TASK: Chart 3 - Projects by Category (Doughnut Chart)
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($projects_by_category, 'name')); ?>,
        datasets: [{
            label: 'Projects',
            data: <?php echo json_encode(array_column($projects_by_category, 'count')); ?>,
            backgroundColor: [
                'rgba(200, 16, 46, 0.8)',
                'rgba(71, 112, 80, 0.8)',
                'rgba(23, 162, 184, 0.8)',
                'rgba(255, 193, 7, 0.8)'
            ],
            borderWidth: 3,
            borderColor: '#fff',
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    font: {
                        size: 13
                    },
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                borderRadius: 6,
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return `${label}: ${value} projects (${percentage}%)`;
                    }
                }
            }
        }
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

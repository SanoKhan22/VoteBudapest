<?php
/**
 * Vote Action Handler
 * CORE TASKS:
 * - Vote/unvote on projects
 * - Max 3 votes per category enforcement (1.0 pt)
 * - Only within 2 weeks of approval (1.0 pt)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Require login
require_login();

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$project_id = $_POST['project_id'] ?? null;
$action = $_POST['action'] ?? 'vote'; // 'vote' or 'unvote'

if (!$project_id || !is_numeric($project_id)) {
    echo json_encode(['success' => false, 'error' => 'Invalid project ID']);
    exit;
}

try {
    $db = getDB();
    
    // Get project details
    $stmt = $db->prepare("
        SELECT p.*, c.id as category_id
        FROM projects p
        JOIN categories c ON p.category_id = c.id
        WHERE p.id = ? AND p.status_id = ?
    ");
    $stmt->execute([$project_id, STATUS_APPROVED]);
    $project = $stmt->fetch();
    
    if (!$project) {
        echo json_encode(['success' => false, 'error' => 'Project not found or not available for voting']);
        exit;
    }
    
    // CORE TASK: Check if voting period is still open (2 weeks)
    if (!is_voting_open($project['approved_at'])) {
        echo json_encode(['success' => false, 'error' => 'Voting period has closed for this project']);
        exit;
    }
    
    $user_id = get_user_id();
    
    if ($action === 'vote') {
        // Check if user already voted for this project
        $stmt = $db->prepare("SELECT id FROM votes WHERE user_id = ? AND project_id = ?");
        $stmt->execute([$user_id, $project_id]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'You have already voted for this project']);
            exit;
        }
        
        // CORE TASK: Check if user has reached max 3 votes per category
        $stmt = $db->prepare("
            SELECT COUNT(*) as vote_count
            FROM votes v
            JOIN projects p ON v.project_id = p.id
            WHERE v.user_id = ? AND p.category_id = ?
        ");
        $stmt->execute([$user_id, $project['category_id']]);
        $result = $stmt->fetch();
        
        if ($result['vote_count'] >= MAX_VOTES_PER_CATEGORY) {
            echo json_encode([
                'success' => false, 
                'error' => 'You have already cast ' . MAX_VOTES_PER_CATEGORY . ' votes in this category'
            ]);
            exit;
        }
        
        // Add vote
        $stmt = $db->prepare("INSERT INTO votes (user_id, project_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $project_id]);
        
        $message = 'Vote added successfully';
        
    } else if ($action === 'unvote') {
        // Remove vote
        $stmt = $db->prepare("DELETE FROM votes WHERE user_id = ? AND project_id = ?");
        $stmt->execute([$user_id, $project_id]);
        
        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'error' => 'You have not voted for this project']);
            exit;
        }
        
        $message = 'Vote removed successfully';
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        exit;
    }
    
    // Get updated vote count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM votes WHERE project_id = ?");
    $stmt->execute([$project_id]);
    $vote_count = $stmt->fetch()['count'];
    
    // Get remaining votes for this category
    $stmt = $db->prepare("
        SELECT COUNT(*) as vote_count
        FROM votes v
        JOIN projects p ON v.project_id = p.id
        WHERE v.user_id = ? AND p.category_id = ?
    ");
    $stmt->execute([$user_id, $project['category_id']]);
    $used_votes = $stmt->fetch()['vote_count'];
    $remaining_votes = MAX_VOTES_PER_CATEGORY - $used_votes;
    
    // Return JSON response for AJAX (EXTRA TASK: 1.0 pt)
    echo json_encode([
        'success' => true,
        'message' => $message,
        'vote_count' => $vote_count,
        'remaining_votes' => $remaining_votes,
        'user_voted' => ($action === 'vote'),
        'category_id' => $project['category_id']
    ]);
    
} catch (PDOException $e) {
    error_log("Vote error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

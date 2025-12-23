<?php
/**
 * Utility Functions
 * General helper functions for the application
 */

/**
 * Sanitize output to prevent XSS attacks
 * 
 * @param string $string String to sanitize
 * @return string Sanitized string
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a URL
 * 
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Format date for display
 * 
 * @param string $date Date string
 * @param string $format Date format (default: DATE_FORMAT)
 * @return string Formatted date
 */
function format_date($date, $format = DATE_FORMAT) {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Get time ago string (e.g., "2 days ago")
 * 
 * @param string $datetime Date/time string
 * @return string Time ago string
 */
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    if ($diff < 2592000) return floor($diff / 604800) . ' weeks ago';
    
    return format_date($datetime, DATE_FORMAT_SHORT);
}

/**
 * Check if voting is still open for a project
 * 
 * @param string $approved_at Approval timestamp
 * @return bool True if voting is open
 */
function is_voting_open($approved_at) {
    if (empty($approved_at)) return false;
    
    $approval_timestamp = strtotime($approved_at);
    $deadline_timestamp = $approval_timestamp + (VOTING_PERIOD_DAYS * 24 * 60 * 60);
    
    return time() <= $deadline_timestamp;
}

/**
 * Get days remaining for voting
 * 
 * @param string $approved_at Approval timestamp
 * @return int Days remaining (0 if closed)
 */
function voting_days_remaining($approved_at) {
    if (empty($approved_at)) return 0;
    
    $approval_timestamp = strtotime($approved_at);
    $deadline_timestamp = $approval_timestamp + (VOTING_PERIOD_DAYS * 24 * 60 * 60);
    $remaining_seconds = $deadline_timestamp - time();
    
    if ($remaining_seconds <= 0) return 0;
    
    return ceil($remaining_seconds / (24 * 60 * 60));
}

/**
 * Get status badge HTML
 * 
 * @param int $status_id Status ID
 * @param string $status_name Status name
 * @return string HTML for status badge
 */
function get_status_badge($status_id, $status_name) {
    $badges = [
        STATUS_PENDING => 'warning',
        STATUS_APPROVED => 'success',
        STATUS_REJECTED => 'danger',
        STATUS_REWORK => 'info',
    ];
    
    $class = $badges[$status_id] ?? 'secondary';
    return '<span class="badge bg-' . $class . '">' . e($status_name) . '</span>';
}

/**
 * Get category badge color
 * 
 * @param int $category_id Category ID
 * @return string Bootstrap color class
 */
function get_category_color($category_id) {
    $colors = [
        CATEGORY_LOCAL_SMALL => 'primary',
        CATEGORY_LOCAL_LARGE => 'success',
        CATEGORY_EQUAL_OPPORTUNITY => 'info',
        CATEGORY_GREEN_BUDAPEST => 'success',
    ];
    
    return $colors[$category_id] ?? 'secondary';
}

/**
 * Truncate text to specified length
 * 
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $suffix Suffix to add (default: '...')
 * @return string Truncated text
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (function_exists('mb_strlen') && mb_strlen($text) <= $length) {
        return $text;
    } elseif (strlen($text) <= $length) {
        return $text;
    }
    
    if (function_exists('mb_substr')) {
        return mb_substr($text, 0, $length) . $suffix;
    } else {
        return substr($text, 0, $length) . $suffix;
    }
}

/**
 * Generate URL-safe slug from string
 * 
 * @param string $string String to slugify
 * @return string Slug
 */
function slugify($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Set flash message
 * 
 * @param string $type Message type (success, error, warning, info)
 * @param string $message Message text
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * 
 * @return array|null Flash message array or null
 */
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message as Bootstrap alert
 * 
 * @return string HTML for flash message
 */
function display_flash() {
    $flash = get_flash();
    if ($flash === null) return '';
    
    $alert_types = [
        'success' => 'success',
        'error' => 'danger',
        'warning' => 'warning',
        'info' => 'info',
    ];
    
    $type = $alert_types[$flash['type']] ?? 'info';
    
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">' .
           e($flash['message']) .
           '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' .
           '</div>';
}

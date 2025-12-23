<?php
/**
 * Form Validation Functions
 * Validation rules based on assignment requirements
 */

/**
 * Validate username
 * Requirements: Must be unique, cannot contain spaces
 * 
 * @param string $username Username to validate
 * @param int|null $user_id Current user ID (for updates)
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validate_username($username, $user_id = null) {
    // Check if empty
    if (empty($username)) {
        return ['valid' => false, 'error' => 'Username is required'];
    }
    
    // CORE TASK: Username cannot contain spaces (0.5 pts)
    if (preg_match('/\s/', $username)) {
        return ['valid' => false, 'error' => 'Username cannot contain spaces'];
    }
    
    // Check length (reasonable limits)
    if (strlen($username) < 3) {
        return ['valid' => false, 'error' => 'Username must be at least 3 characters'];
    }
    
    if (strlen($username) > 50) {
        return ['valid' => false, 'error' => 'Username must not exceed 50 characters'];
    }
    
    // MINIMUM REQUIREMENT: Username must be unique
    $db = getDB();
    $sql = "SELECT id FROM users WHERE username = ?";
    
    if ($user_id !== null) {
        $sql .= " AND id != ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$username, $user_id]);
    } else {
        $stmt = $db->prepare($sql);
        $stmt->execute([$username]);
    }
    
    if ($stmt->fetch()) {
        return ['valid' => false, 'error' => 'Username already exists'];
    }
    
    return ['valid' => true, 'error' => null];
}

/**
 * Validate email
 * Requirements: Must have valid format
 * 
 * @param string $email Email to validate
 * @param int|null $user_id Current user ID (for updates)
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validate_email($email, $user_id = null) {
    // Check if empty
    if (empty($email)) {
        return ['valid' => false, 'error' => 'Email is required'];
    }
    
    // CORE TASK: Email format must be valid (0.5 pts)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['valid' => false, 'error' => 'Email format is invalid'];
    }
    
    // Check if email already exists
    $db = getDB();
    $sql = "SELECT id FROM users WHERE email = ?";
    
    if ($user_id !== null) {
        $sql .= " AND id != ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$email, $user_id]);
    } else {
        $stmt = $db->prepare($sql);
        $stmt->execute([$email]);
    }
    
    if ($stmt->fetch()) {
        return ['valid' => false, 'error' => 'Email already exists'];
    }
    
    return ['valid' => true, 'error' => null];
}

/**
 * Validate password
 * Requirements: 
 * - Minimum 8 characters (MANDATORY)
 * - Must include lowercase, uppercase, and numeric characters (CORE 0.5 pts)
 * 
 * @param string $password Password to validate
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validate_password($password) {
    // Check if empty
    if (empty($password)) {
        return ['valid' => false, 'error' => 'Password is required'];
    }
    
    // MINIMUM REQUIREMENT: Password at least 8 characters
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return ['valid' => false, 'error' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
    }
    
    // CORE TASK: Password must include lowercase, uppercase, and numeric (0.5 pts)
    if (!preg_match('/[a-z]/', $password)) {
        return ['valid' => false, 'error' => 'Password must include at least one lowercase letter'];
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        return ['valid' => false, 'error' => 'Password must include at least one uppercase letter'];
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        return ['valid' => false, 'error' => 'Password must include at least one number'];
    }
    
    return ['valid' => true, 'error' => null];
}

/**
 * Validate password confirmation
 * Requirements: Two password fields must match
 * 
 * @param string $password Password
 * @param string $password_confirm Password confirmation
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validate_password_confirmation($password, $password_confirm) {
    // CORE TASK: Two password fields must match (0.5 pts)
    if ($password !== $password_confirm) {
        return ['valid' => false, 'error' => 'Passwords do not match'];
    }
    
    return ['valid' => true, 'error' => null];
}

/**
 * Validate project title
 * Requirements: Minimum 10 characters (MANDATORY)
 * 
 * @param string $title Title to validate
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validate_project_title($title) {
    if (empty($title)) {
        return ['valid' => false, 'error' => 'Project title is required'];
    }
    
    // MINIMUM REQUIREMENT: Project title minimum 10 characters
    if (strlen($title) < PROJECT_TITLE_MIN_LENGTH) {
        return ['valid' => false, 'error' => 'Project title must be at least ' . PROJECT_TITLE_MIN_LENGTH . ' characters'];
    }
    
    if (strlen($title) > 255) {
        return ['valid' => false, 'error' => 'Project title must not exceed 255 characters'];
    }
    
    return ['valid' => true, 'error' => null];
}

/**
 * Validate project description
 * Requirements: Minimum 150 characters (MANDATORY)
 * 
 * @param string $description Description to validate
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validate_project_description($description) {
    if (empty($description)) {
        return ['valid' => false, 'error' => 'Project description is required'];
    }
    
    // MINIMUM REQUIREMENT: Project description minimum 150 characters
    if (strlen($description) < PROJECT_DESC_MIN_LENGTH) {
        return ['valid' => false, 'error' => 'Project description must be at least ' . PROJECT_DESC_MIN_LENGTH . ' characters'];
    }
    
    return ['valid' => true, 'error' => null];
}

/**
 * Validate postal code
 * Requirements (CORE TASK 1.0 pts):
 * - Partial (0.5 pts): any 4-digit number >= 1000
 * - Full (1.0 pts): first digit 1, next two 01-23, last digit 1-9, plus 1007 allowed
 * 
 * @param string $postal_code Postal code to validate
 * @param bool $strict Use strict validation for full points
 * @return array ['valid' => bool, 'error' => string|null, 'points' => float]
 */
function validate_postal_code($postal_code, $strict = true) {
    if (empty($postal_code)) {
        return ['valid' => false, 'error' => 'Postal code is required', 'points' => 0];
    }
    
    // Must be 4 digits
    if (!preg_match('/^\d{4}$/', $postal_code)) {
        return ['valid' => false, 'error' => 'Postal code must be 4 digits', 'points' => 0];
    }
    
    $code = (int)$postal_code;
    
    if ($strict) {
        // FULL VALIDATION (1.0 pts):
        // - First digit: 1 (Budapest)
        // - Second + third digits: 01-23 (district number)
        // - Fourth digit: 1-9
        // - Additionally: 1007 is valid (Margaret Island)
        
        if ($postal_code === '1007') {
            return ['valid' => true, 'error' => null, 'points' => 1.0];
        }
        
        if (!preg_match('/^1(0[1-9]|1\d|2[0-3])[1-9]$/', $postal_code)) {
            return [
                'valid' => false, 
                'error' => 'Invalid Budapest postal code. Must be 1XXX where XX is district 01-23 and last digit is 1-9 (or 1007)',
                'points' => 0
            ];
        }
        
        return ['valid' => true, 'error' => null, 'points' => 1.0];
    } else {
        // PARTIAL VALIDATION (0.5 pts): any 4-digit number >= 1000
        if ($code < 1000) {
            return ['valid' => false, 'error' => 'Postal code must be at least 1000', 'points' => 0];
        }
        
        return ['valid' => true, 'error' => null, 'points' => 0.5];
    }
}

/**
 * Validate category ID
 * Requirements: Category selectable only from fixed list (CORE 0.5 pts)
 * 
 * @param int $category_id Category ID to validate
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validate_category($category_id) {
    if (empty($category_id)) {
        return ['valid' => false, 'error' => 'Category is required'];
    }
    
    // CORE TASK: Category selectable only from fixed list (0.5 pts)
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    
    if (!$stmt->fetch()) {
        return ['valid' => false, 'error' => 'Invalid category selected'];
    }
    
    return ['valid' => true, 'error' => null];
}

/**
 * Validate image upload
 * Requirements: Image URL optional, but if provided, must be valid (CORE 0.5 pts)
 * 
 * @param array $file $_FILES['image'] array
 * @return array ['valid' => bool, 'error' => string|null, 'path' => string|null]
 */
function validate_image_upload($file) {
    // CORE TASK: Image optional (0.5 pts)
    if (empty($file['name'])) {
        return ['valid' => true, 'error' => null, 'path' => null];
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'Image upload failed', 'path' => null];
    }
    
    // Check file size (max 5MB)
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        return ['valid' => false, 'error' => 'Image size must not exceed 5MB', 'path' => null];
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, ALLOWED_IMAGE_TYPES)) {
        return ['valid' => false, 'error' => 'Invalid image format. Allowed: JPG, PNG, GIF, WebP', 'path' => null];
    }
    
    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_IMAGE_EXTENSIONS)) {
        return ['valid' => false, 'error' => 'Invalid file extension', 'path' => null];
    }
    
    // Generate unique filename
    $filename = uniqid('project_', true) . '.' . $extension;
    $upload_path = UPLOAD_DIR . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['valid' => false, 'error' => 'Failed to save image', 'path' => null];
    }
    
    // Return relative path for database storage
    return ['valid' => true, 'error' => null, 'path' => 'uploads/' . $filename];
}

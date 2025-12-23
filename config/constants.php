<?php
/**
 * Application Constants
 * Global configuration values for VoteBudapest
 */

// Application settings
define('APP_NAME', 'VoteBudapest');
define('APP_TAGLINE', 'Budapest Community Budget');

// File upload settings
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Voting settings (from assignment)
define('VOTING_PERIOD_DAYS', 14); // 2 weeks after publication
define('MAX_VOTES_PER_CATEGORY', 3); // User can cast max 3 votes per category

// Pagination (for future use)
define('PROJECTS_PER_PAGE', 12);

// Session settings
define('SESSION_NAME', 'votabudapest_session');
define('SESSION_LIFETIME', 86400); // 24 hours

// Status IDs (must match database)
define('STATUS_PENDING', 1);
define('STATUS_APPROVED', 2);
define('STATUS_REJECTED', 3);
define('STATUS_REWORK', 4);

// Category IDs (must match database)
define('CATEGORY_LOCAL_SMALL', 1);
define('CATEGORY_LOCAL_LARGE', 2);
define('CATEGORY_EQUAL_OPPORTUNITY', 3);
define('CATEGORY_GREEN_BUDAPEST', 4);

// Date format
define('DATE_FORMAT', 'Y-m-d H:i');
define('DATE_FORMAT_SHORT', 'Y-m-d');

// Project validation (from assignment requirements)
define('PROJECT_TITLE_MIN_LENGTH', 10);
define('PROJECT_DESC_MIN_LENGTH', 150);
define('PASSWORD_MIN_LENGTH', 8);

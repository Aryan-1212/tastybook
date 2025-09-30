<?php
/**
 * Common Functions
 * Utility functions used throughout the application
 */

require_once __DIR__ . '/../db/database.php';

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email address
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate password strength
 */
function validatePassword($password) {
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return false;
    }
    return true;
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        try {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            // Fallback if random_bytes unavailable
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
        }
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    // Check if token has expired
    if (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_LIFETIME) {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Start session with security settings
 */
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Configure session security
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        session_start();
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = new Database();
    $stmt = $db->prepare("SELECT id, username, email, first_name, last_name, profile_image, bio, role, points_total FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([getCurrentUserId()]);
    return $stmt->fetch();
}

/**
 * Check if current user is admin
 */
function isAdmin() {
    $user = getCurrentUser();
    return $user && isset($user['role']) && $user['role'] === 'admin';
}

/**
 * Get user by id
 */
function getUserById($userId) {
    $db = new Database();
    $stmt = $db->prepare("SELECT id, username, email, first_name, last_name, role, points_total, profile_image FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

/**
 * Compute badge/level from total points
 */
function getBadgeForPoints($points) {
    if ($points >= 1000) return 'Gold Chef';
    if ($points >= 500) return 'Silver Chef';
    if ($points >= 200) return 'Bronze Chef';
    if ($points >= 50) return 'Beginner';
    return 'Newbie';
}

/**
 * Award points and record a transaction if not already awarded for unique actions
 * $action examples: recipe_approved, favorite_received, review_received, bonus_10_favorites, streak_5_days, top_of_week
 */
function awardPoints($userId, $points, $action, $recipeId = null, $meta = []) {
    try {
        $db = new Database();
        $db->beginTransaction();

        // Ensure uniqueness for (user, recipe, action)
        $stmt = $db->prepare("INSERT INTO points_transactions (user_id, recipe_id, action, points, meta, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $userId,
            $recipeId,
            $action,
            $points,
            json_encode($meta)
        ]);

        // Update user's cached total
        $stmt = $db->prepare("UPDATE users SET points_total = points_total + ? WHERE id = ?");
        $stmt->execute([$points, $userId]);

        $db->commit();
        return true;
    } catch (Exception $e) {
        if (method_exists($db ?? null, 'rollback')) {
            $db->rollback();
        }
        // Likely duplicate unique key - ignore silently
        error_log('awardPoints skipped: ' . $e->getMessage());
        return false;
    }
}

/**
 * Calculate favorites count for a recipe
 */
function getFavoritesCount($recipeId) {
    $db = new Database();
    $stmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE recipe_id = ?");
    $stmt->execute([$recipeId]);
    return (int)$stmt->fetchColumn();
}

/**
 * Check and award 10 favorites bonus
 */
function maybeAwardFavoritesBonus($recipeId, $ownerUserId) {
    $count = getFavoritesCount($recipeId);
    if ($count >= 10) {
        awardPoints($ownerUserId, 20, 'bonus_10_favorites', $recipeId, ['count' => $count]);
    }
}

/**
 * Check 5-day consecutive approvals streak for a user and award once when achieved
 */
function maybeAwardFiveDayStreak($userId) {
    $db = new Database();
    // Get approvals in last 7 days
    $stmt = $db->prepare("SELECT DATE(approved_at) as d FROM recipes WHERE user_id = ? AND approval_status = 'approved' AND approved_at IS NOT NULL AND approved_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(approved_at) ORDER BY d DESC");
    $stmt->execute([$userId]);
    $days = $stmt->fetchAll();
    $dates = array_map(function($row){ return $row['d']; }, $days);
    // Count consecutive days ending today
    $streak = 0;
    for ($i = 0; $i < 7; $i++) {
        $date = date('Y-m-d', strtotime("-{$i} day"));
        if (in_array($date, $dates)) {
            $streak++;
        } else {
            break;
        }
    }
    if ($streak >= 5) {
        $key = 'streak5_' . date('Ymd');
        try {
            $stmt = $db->prepare("INSERT INTO user_achievements (user_id, key_name, details, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$userId, $key, json_encode(['streak' => $streak])]);
            awardPoints($userId, 15, 'streak_5_days', null, ['streak' => $streak]);
        } catch (Exception $e) {
            error_log('streak check: ' . $e->getMessage());
        }
    }
}

/**
 * Redirect to a page
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Set flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Get and clear flash message
 */
function getFlashMessage($type) {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

/**
 * Format date
 */
function formatDate($date, $format = 'M j, Y') {
    return date($format, strtotime($date));
}

/**
 * Format time ago
 */
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    return floor($time/31536000) . ' years ago';
}

/**
 * Upload file
 */
function uploadFile($file, $uploadDir = UPLOAD_PATH) {
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new Exception('Invalid file upload.');
    }
    
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return null;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new Exception('File too large.');
        default:
            throw new Exception('Unknown upload error.');
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('File too large.');
    }
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Invalid file type.');
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to move uploaded file.');
    }
    
    return $filename;
}

/**
 * Delete file
 */
function deleteFile($filename, $uploadDir = UPLOAD_PATH) {
    $filepath = $uploadDir . $filename;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Generate pagination
 */
function generatePagination($currentPage, $totalPages, $baseUrl) {
    $pagination = '<div class="pagination">';
    
    // Previous button
    if ($currentPage > 1) {
        $pagination .= '<a href="' . $baseUrl . '?page=' . ($currentPage - 1) . '" class="pagination-btn">Previous</a>';
    }
    
    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $active = ($i == $currentPage) ? ' active' : '';
        $pagination .= '<a href="' . $baseUrl . '?page=' . $i . '" class="pagination-btn' . $active . '">' . $i . '</a>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $pagination .= '<a href="' . $baseUrl . '?page=' . ($currentPage + 1) . '" class="pagination-btn">Next</a>';
    }
    
    $pagination .= '</div>';
    return $pagination;
}

/**
 * Log activity
 */
function logActivity($action, $details = '') {
    try {
        $db = new Database();
        // activity_log may not exist on fresh installs; ignore failures
        $stmt = $db->prepare("INSERT INTO activity_log (user_id, action, details, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            getCurrentUserId(),
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (Exception $e) {
        error_log('logActivity skipped: ' . $e->getMessage());
    }
}

/**
 * Send JSON response
 */
function sendJSONResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/**
 * Validate required fields
 */
function validateRequired($data, $fields) {
    $errors = [];
    foreach ($fields as $field) {
        if (empty($data[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        }
    }
    return $errors;
}

/**
 * Clean HTML content
 */
function cleanHTML($content) {
    // If HTMLPurifier is available, use it. Otherwise do a lightweight cleanup.
    if (class_exists('HTMLPurifier') && class_exists('HTMLPurifier_Config')) {
        $configClass = 'HTMLPurifier_Config';
        $config = forward_static_call([$configClass, 'createDefault']);
        $purifierClass = 'HTMLPurifier';
        $purifier = new $purifierClass($config);
        return $purifier->purify($content);
    }

    // Basic cleanup: strip dangerous tags and attributes
    $allowedTags = '<p><a><br><strong><em><ul><ol><li><img><h1><h2><h3><h4><h5><h6><blockquote><code>';
    $clean = strip_tags($content, $allowedTags);
    // remove on* attributes (onclick, onerror, etc.)
    $clean = preg_replace('/on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean);
    // remove javascript: URIs
    $clean = preg_replace('/href\s*=\s*("javascript:[^"]*"|\'javascript:[^\']*\'|javascript:[^\s>]+)/i', 'href="#"', $clean);
    return $clean;
}
?>

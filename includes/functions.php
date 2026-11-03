<?php
/**
 * Helper Functions
 * Online Classifieds Marketplace
 *
 * Beginner-friendly procedural helper library covering security,
 * sessions, CSRF, flash notifications, formatting, and file uploads.
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Initializes a secure session if not already active.
 */
function init_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        // Enforce cookie security attributes
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

// Start session automatically upon including functions
init_session();

/**
 * Escapes HTML characters to prevent Cross-Site Scripting (XSS).
 *
 * @param string|null $value
 * @return string
 */
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Returns the base URL of the application.
 *
 * @param string $path Optional path to append
 * @return string
 */
function base_url(string $path = ''): string {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $base = '/online-classifieds';
    
    // If running in a nested or customized directory
    if (strpos($scriptName, '/online-classifieds') === 0) {
        $base = '/online-classifieds';
    }

    $path = ltrim($path, '/');
    return $path ? "$base/$path" : $base;
}

/**
 * Returns the web URL for a listing image.
 *
 * @param string|null $filename
 * @return string
 */
function listing_image_url(?string $filename): string {
    if (!empty($filename)) {
        $path = __DIR__ . '/../uploads/listings/' . $filename;
        if (file_exists($path)) {
            return base_url('uploads/listings/' . $filename);
        }
    }
    return base_url('uploads/listings/default-placeholder.svg');
}

/**
 * Formats a decimal amount into a currency string ($XX.XX).
 *
 * @param float|string $amount
 * @return string
 */
function format_price($amount): string {
    return '$' . number_format((float)$amount, 2);
}

/**
 * Formats a timestamp into a human-friendly relative time (e.g. "2 hours ago").
 *
 * @param string $datetime
 * @return string
 */
function time_ago(string $datetime): string {
    $timestamp = strtotime($datetime);
    if (!$timestamp) {
        return $datetime;
    }
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return 'just now';
    }
    if ($diff < 3600) {
        $mins = (int)($diff / 60);
        return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
    }
    if ($diff < 86400) {
        $hours = (int)($diff / 3600);
        return $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ago';
    }
    if ($diff < 2592000) {
        $days = (int)($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    }
    return date('M j, Y', $timestamp);
}

/* ============================================================
 * Flash Notifications
 * ============================================================ */

/**
 * Stores a flash message in the session for the next page load.
 *
 * @param string $type success|danger|warning|info
 * @param string $message
 */
function set_flash(string $type, string $message): void {
    init_session();
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

/**
 * Retrieves and clears the flash message from the session.
 *
 * @return array|null
 */
function get_flash(): ?array {
    init_session();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/* ============================================================
 * CSRF Protection
 * ============================================================ */

/**
 * Generates or retrieves the active CSRF token.
 *
 * @return string
 */
function generate_csrf_token(): string {
    init_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Generates an HTML hidden input element containing the CSRF token.
 *
 * @return string
 */
function csrf_field(): string {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Verifies a submitted CSRF token against the session token.
 *
 * @param string|null $token
 * @return bool
 */
function verify_csrf_token(?string $token): bool {
    init_session();
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/* ============================================================
 * Authentication Helpers
 * ============================================================ */

/**
 * Checks if a user is currently authenticated.
 *
 * @return bool
 */
function is_logged_in(): bool {
    init_session();
    return !empty($_SESSION['user_id']);
}

/**
 * Returns the currently authenticated user's session data.
 *
 * @return array|null
 */
function current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'       => $_SESSION['user_id'],
        'name'     => $_SESSION['user_name'] ?? 'User',
        'email'    => $_SESSION['user_email'] ?? '',
        'phone'    => $_SESSION['user_phone'] ?? '',
        'location' => $_SESSION['user_location'] ?? '',
    ];
}

/**
 * Logs in a user, regenerates session ID to prevent fixation, and stores info.
 *
 * @param array $user
 */
function login_user(array $user): void {
    init_session();
    // Security: regenerate session ID to defend against session fixation
    session_regenerate_id(true);

    $_SESSION['user_id']       = (int)$user['id'];
    $_SESSION['user_name']     = $user['name'];
    $_SESSION['user_email']    = $user['email'];
    $_SESSION['user_phone']    = $user['phone'] ?? '';
    $_SESSION['user_location'] = $user['location'] ?? '';
}

/**
 * Logs out the current user and clears session data.
 */
function logout_user(): void {
    init_session();
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_phone']);
    unset($_SESSION['user_location']);
    session_regenerate_id(true);
}

/**
 * Ensures the user is logged in; redirects to login page if unauthenticated.
 */
function require_auth(): void {
    if (!is_logged_in()) {
        set_flash('warning', 'Please log in to continue.');
        header('Location: ' . base_url('auth/login.php'));
        exit;
    }
}

/* ============================================================
 * Safe File Upload Handling
 * ============================================================ */

/**
 * Validates and securely saves an uploaded image file.
 *
 * Checks:
 * 1. Upload errors
 * 2. File size (max 3 MB)
 * 3. MIME type inspection via fileinfo
 * 4. Allowed file extension whitelist
 * 5. Cryptographically secure random filename to prevent collisions / directory traversal
 *
 * @param array $file The $_FILES['image'] array
 * @param array &$errors Output array for validation errors
 * @return string|null Generated filename on success, null on failure/none
 */
function handle_image_upload(array $file, array &$errors): ?string {
    // If no file was uploaded
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload failed with error code: ' . $file['error'];
        return null;
    }

    // 1. File size check (max 3MB = 3 * 1024 * 1024 bytes)
    $maxBytes = 3 * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        $errors[] = 'Image size must be 3MB or less.';
        return null;
    }

    // 2. MIME type verification using finfo
    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!array_key_exists($mimeType, $allowedMimes)) {
        $errors[] = 'Invalid image type. Only JPG, PNG, and WebP files are allowed.';
        return null;
    }

    // 3. Extension verification
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $validExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $validExtensions, true)) {
        $errors[] = 'Invalid file extension.';
        return null;
    }

    // Normalize jpeg to jpg
    if ($ext === 'jpeg') {
        $ext = 'jpg';
    }

    // 4. Generate unique, random filename
    $uploadDir = __DIR__ . '/../uploads/listings/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $newFilename = 'listing_' . bin2hex(random_bytes(10)) . '.' . $ext;
    $destination = $uploadDir . $newFilename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $errors[] = 'Failed to save uploaded image. Please check directory write permissions.';
        return null;
    }

    return $newFilename;
}

<?php
/**
 * Delete Listing Handler
 * Online Classifieds Marketplace
 */
require_once __DIR__ . '/../includes/functions.php';

// Authentication required
require_auth();

// Only allow POST requests for deletion (prevents CSRF via GET links)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('danger', 'Invalid request method for deletion.');
    header('Location: ' . base_url('dashboard/'));
    exit;
}

// Verify CSRF Token
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('danger', 'Security validation failed (Invalid CSRF token). Please try again.');
    header('Location: ' . base_url('dashboard/'));
    exit;
}

$listingId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$currentUser = current_user();
$pdo = get_db_connection();

if (!$pdo || $listingId <= 0) {
    set_flash('danger', 'Invalid listing request.');
    header('Location: ' . base_url('dashboard/'));
    exit;
}

try {
    // 1. Fetch listing to confirm ownership and inspect image
    $stmt = $pdo->prepare('SELECT * FROM listings WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $listingId]);
    $listing = $stmt->fetch();

    if (!$listing) {
        set_flash('danger', 'Listing not found.');
        header('Location: ' . base_url('dashboard/'));
        exit;
    }

    // 2. Strict authorization check
    if ((int)$listing['user_id'] !== (int)$currentUser['id']) {
        set_flash('danger', 'Unauthorized: You can only delete your own listings.');
        header('Location: ' . base_url('dashboard/'));
        exit;
    }

    // 3. Delete from database
    $deleteStmt = $pdo->prepare('DELETE FROM listings WHERE id = :id AND user_id = :user_id');
    $deleteStmt->execute([
        ':id'      => $listingId,
        ':user_id' => $currentUser['id'],
    ]);

    // 4. Clean up uploaded image if custom (do not delete sample svgs)
    if (!empty($listing['image_path']) && strpos($listing['image_path'], 'listing_') === 0) {
        $filePath = __DIR__ . '/../uploads/listings/' . $listing['image_path'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    set_flash('success', 'Listing "' . e($listing['title']) . '" has been removed.');
    header('Location: ' . base_url('dashboard/'));
    exit;
} catch (PDOException $e) {
    error_log('Delete listing error: ' . $e->getMessage());
    set_flash('danger', 'A database error occurred while deleting the listing.');
    header('Location: ' . base_url('dashboard/'));
    exit;
}

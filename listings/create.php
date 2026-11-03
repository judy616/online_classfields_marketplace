<?php
/**
 * Create New Listing
 * Online Classifieds Marketplace
 */
$pageTitle = 'Post an Advertisement';
require_once __DIR__ . '/../includes/header.php';

// Authentication required
require_auth();

$pdo = get_db_connection();
$categories = [];

if ($pdo) {
    try {
        $catStmt = $pdo->query('SELECT * FROM categories ORDER BY name ASC');
        $categories = $catStmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Categories fetch error: ' . $e->getMessage());
    }
}

$errors = [];
$title = '';
$categoryId = '';
$price = '';
$location = $currentUser['location'] ?? '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid or expired session token. Please try submitting again.';
    }

    // 2. Collect inputs
    $title       = trim($_POST['title'] ?? '');
    $categoryId  = (int)($_POST['category_id'] ?? 0);
    $price       = trim($_POST['price'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // 3. Validation
    if (empty($title) || strlen($title) < 5 || strlen($title) > 150) {
        $errors[] = 'Title is required (between 5 and 150 characters).';
    }

    if ($categoryId <= 0) {
        $errors[] = 'Please select a valid category.';
    }

    if (!is_numeric($price) || (float)$price < 0) {
        $errors[] = 'Please enter a valid non-negative price.';
    }

    if (empty($location)) {
        $errors[] = 'Item location or meetup spot is required.';
    }

    if (empty($description) || strlen($description) < 10) {
        $errors[] = 'Please provide a detailed description (at least 10 characters).';
    }

    // 4. Secure File Upload
    $uploadedFilename = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadedFilename = handle_image_upload($_FILES['image'], $errors);
    }

    // 5. Database Insertion
    if (!$pdo) {
        $errors[] = 'Database is currently unavailable. Please verify MySQL service status.';
    } elseif (empty($errors)) {
        try {
            $stmt = $pdo->prepare('
                INSERT INTO listings (user_id, category_id, title, description, price, location, image_path, status, created_at)
                VALUES (:user_id, :category_id, :title, :description, :price, :location, :image_path, "active", NOW())
            ');
            $stmt->execute([
                ':user_id'     => $currentUser['id'],
                ':category_id' => $categoryId,
                ':title'       => $title,
                ':description' => $description,
                ':price'       => (float)$price,
                ':location'    => $location,
                ':image_path'  => $uploadedFilename,
            ]);

            $newId = (int)$pdo->lastInsertId();
            set_flash('success', 'Your advertisement has been published successfully!');
            header('Location: ' . base_url('listings/view.php?id=' . $newId));
            exit;
        } catch (PDOException $e) {
            error_log('Listing create error: ' . $e->getMessage());
            $errors[] = 'A database error occurred while creating your ad. Please try again.';
        }
    }
}
?>

<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
          <li class="breadcrumb-item"><a href="<?= base_url('dashboard/') ?>">Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page">Post Advertisement</li>
        </ol>
      </nav>

      <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
        <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
          <div class="rounded-circle bg-warning-subtle text-warning-emphasis p-3 fs-4">
            <i class="bi bi-tag-fill"></i>
          </div>
          <div>
            <h1 class="h4 fw-bold mb-0">Post a New Advertisement</h1>
            <p class="text-muted small mb-0">Fill in the details below to publish your item to the marketplace</p>
          </div>
        </div>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger shadow-sm mb-4">
            <ul class="mb-0 ps-3">
              <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form action="<?= base_url('listings/create.php') ?>" method="POST" enctype="multipart/form-data" novalidate>
          <?= csrf_field() ?>

          <div class="mb-3">
            <label for="title" class="form-label fw-semibold">Listing Title <span class="text-danger">*</span></label>
            <input type="text" name="title" id="title" class="form-control" value="<?= e($title) ?>" placeholder="e.g. TI-84 Plus CE Graphing Calculator in mint condition" required>
            <div class="form-text">Include key brand, model, and item condition.</div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="category_id" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
              <select name="category_id" id="category_id" class="form-select" required>
                <option value="">Select a Category</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= (int)$cat['id'] ?>" <?= $categoryId === (int)$cat['id'] ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label for="price" class="form-label fw-semibold">Price ($ USD) <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" step="0.01" min="0" name="price" id="price" class="form-control" value="<?= e($price) ?>" placeholder="0.00" required>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="location" class="form-label fw-semibold">Location / Meetup Spot <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
              <input type="text" name="location" id="location" class="form-control" value="<?= e($location) ?>" placeholder="e.g. Science Library, Student Union, North Quad" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
            <textarea name="description" id="description" rows="5" class="form-control" placeholder="Describe the item's condition, age, included accessories, reasons for selling..." required><?= e($description) ?></textarea>
          </div>

          <!-- Image Upload with Instant Preview -->
          <div class="mb-4">
            <label for="listingImageInput" class="form-label fw-semibold">Item Photo</label>
            <input type="file" name="image" id="listingImageInput" class="form-control" accept="image/jpeg,image/png,image/webp">
            <div class="form-text">Allowed formats: JPG, PNG, WebP (Max size: 3MB).</div>

            <div id="imagePreviewContainer" class="mt-3 d-none">
              <label class="form-label small fw-semibold text-muted">Image Preview:</label>
              <div style="max-width: 320px; max-height: 220px; overflow: hidden;" class="rounded border p-1 bg-light">
                <img id="imagePreview" src="#" alt="Upload Preview" class="img-fluid rounded">
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center pt-3 border-top">
            <a href="<?= base_url('dashboard/') ?>" class="btn btn-outline-secondary">
              Cancel
            </a>
            <button type="submit" class="btn btn-primary fw-bold px-4">
              <i class="bi bi-check-circle me-1"></i> Publish Advertisement
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
/**
 * Edit Existing Listing
 * Online Classifieds Marketplace
 */
$pageTitle = 'Edit Advertisement';
require_once __DIR__ . '/../includes/header.php';

// Authentication required
require_auth();

$pdo = get_db_connection();
$listingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$listing = null;
$categories = [];

if (!$pdo) {
    echo '<div class="container py-5"><div class="alert alert-danger">Database connection error.</div></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

try {
    // 1. Fetch listing
    $stmt = $pdo->prepare('SELECT * FROM listings WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $listingId]);
    $listing = $stmt->fetch();

    // 2. Fetch categories
    $catStmt = $pdo->query('SELECT * FROM categories ORDER BY name ASC');
    $categories = $catStmt->fetchAll();
} catch (PDOException $e) {
    error_log('Edit listing fetch error: ' . $e->getMessage());
}

if (!$listing) {
    set_flash('danger', 'Listing not found.');
    header('Location: ' . base_url('dashboard/'));
    exit;
}

// Authorization check: only owner can edit
if ((int)$listing['user_id'] !== (int)$currentUser['id']) {
    set_flash('danger', 'Unauthorized access: You can only edit your own listings.');
    header('Location: ' . base_url('dashboard/'));
    exit;
}

$errors = [];
$title       = $listing['title'];
$categoryId  = (int)$listing['category_id'];
$price       = $listing['price'];
$location    = $listing['location'];
$description = $listing['description'];
$status      = $listing['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid or expired session token. Please try submitting again.';
    }

    $title       = trim($_POST['title'] ?? '');
    $categoryId  = (int)($_POST['category_id'] ?? 0);
    $price       = trim($_POST['price'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status      = trim($_POST['status'] ?? 'active');

    // Validation
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

    if (!in_array($status, ['active', 'sold', 'archived'], true)) {
        $status = 'active';
    }

    // Optional image replacement
    $imagePath = $listing['image_path'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $newUploaded = handle_image_upload($_FILES['image'], $errors);
        if ($newUploaded) {
            $imagePath = $newUploaded;
        }
    }

    if (empty($errors)) {
        try {
            $updateStmt = $pdo->prepare('
                UPDATE listings 
                SET category_id = :category_id,
                    title = :title,
                    description = :description,
                    price = :price,
                    location = :location,
                    image_path = :image_path,
                    status = :status,
                    updated_at = NOW()
                WHERE id = :id AND user_id = :user_id
            ');
            $updateStmt->execute([
                ':category_id' => $categoryId,
                ':title'       => $title,
                ':description' => $description,
                ':price'       => (float)$price,
                ':location'    => $location,
                ':image_path'  => $imagePath,
                ':status'      => $status,
                ':id'          => $listing['id'],
                ':user_id'     => $currentUser['id'],
            ]);

            set_flash('success', 'Listing updated successfully!');
            header('Location: ' . base_url('listings/view.php?id=' . (int)$listing['id']));
            exit;
        } catch (PDOException $e) {
            error_log('Listing update error: ' . $e->getMessage());
            $errors[] = 'A database error occurred while updating the listing.';
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
          <li class="breadcrumb-item"><a href="<?= base_url('listings/view.php?id=' . (int)$listing['id']) ?>"><?= e($listing['title']) ?></a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
      </nav>

      <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
        <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
          <div class="rounded-circle bg-primary-subtle text-primary p-3 fs-4">
            <i class="bi bi-pencil-square"></i>
          </div>
          <div>
            <h1 class="h4 fw-bold mb-0">Edit Listing</h1>
            <p class="text-muted small mb-0">Update price, photos, description, or mark item as sold</p>
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

        <form action="<?= base_url('listings/edit.php?id=' . (int)$listing['id']) ?>" method="POST" enctype="multipart/form-data" novalidate>
          <?= csrf_field() ?>

          <div class="mb-3">
            <label for="title" class="form-label fw-semibold">Listing Title <span class="text-danger">*</span></label>
            <input type="text" name="title" id="title" class="form-control" value="<?= e($title) ?>" required>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label for="category_id" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
              <select name="category_id" id="category_id" class="form-select" required>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= (int)$cat['id'] ?>" <?= $categoryId === (int)$cat['id'] ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label for="price" class="form-label fw-semibold">Price ($ USD) <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" step="0.01" min="0" name="price" id="price" class="form-control" value="<?= e((string)$price) ?>" required>
              </div>
            </div>
            <div class="col-md-4">
              <label for="status" class="form-label fw-semibold">Ad Status</label>
              <select name="status" id="status" class="form-select">
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active (For Sale)</option>
                <option value="sold" <?= $status === 'sold' ? 'selected' : '' ?>>Mark as Sold</option>
                <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>Archived (Hidden)</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label for="location" class="form-label fw-semibold">Location / Meetup Spot <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
              <input type="text" name="location" id="location" class="form-control" value="<?= e($location) ?>" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
            <textarea name="description" id="description" rows="5" class="form-control" required><?= e($description) ?></textarea>
          </div>

          <!-- Existing Photo & Upload New Photo -->
          <div class="mb-4">
            <label class="form-label fw-semibold">Item Photo</label>
            <div class="d-flex align-items-center gap-3 mb-2">
              <img src="<?= listing_image_url($listing['image_path']) ?>" alt="Current photo" style="width: 100px; height: 75px; object-fit: cover;" class="rounded border">
              <div>
                <span class="small text-muted d-block">Current photo: <code><?= e($listing['image_path'] ?: 'None (Default placeholder)') ?></code></span>
                <span class="small text-muted">Upload a new photo only if you wish to replace it.</span>
              </div>
            </div>

            <input type="file" name="image" id="listingImageInput" class="form-control mt-2" accept="image/jpeg,image/png,image/webp">
            <div id="imagePreviewContainer" class="mt-2 d-none">
              <label class="form-label small fw-semibold text-muted">New Photo Preview:</label>
              <div style="max-width: 250px;" class="rounded border p-1 bg-light">
                <img id="imagePreview" src="#" alt="New Preview" class="img-fluid rounded">
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center pt-3 border-top">
            <a href="<?= base_url('listings/view.php?id=' . (int)$listing['id']) ?>" class="btn btn-outline-secondary">
              Cancel
            </a>
            <button type="submit" class="btn btn-primary fw-bold px-4">
              <i class="bi bi-save me-1"></i> Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

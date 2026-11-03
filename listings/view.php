<?php
/**
 * Single Listing Detail View
 * Online Classifieds Marketplace
 */
require_once __DIR__ . '/../includes/header.php';

$pdo = get_db_connection();
$listingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$listing = null;
$related = [];

if ($pdo && $listingId > 0) {
    try {
        $stmt = $pdo->prepare('
            SELECT l.*, c.name AS category_name, c.slug AS category_slug, 
                   u.name AS seller_name, u.email AS seller_email, u.phone AS seller_phone, 
                   u.location AS seller_location, u.created_at AS seller_joined
            FROM listings l
            JOIN categories c ON l.category_id = c.id
            JOIN users u ON l.user_id = u.id
            WHERE l.id = :id
            LIMIT 1
        ');
        $stmt->execute([':id' => $listingId]);
        $listing = $stmt->fetch();

        if ($listing) {
            // Fetch up to 3 related listings in the same category
            $relStmt = $pdo->prepare('
                SELECT l.*, c.name AS category_name
                FROM listings l
                JOIN categories c ON l.category_id = c.id
                WHERE l.category_id = :cat_id AND l.id != :curr_id AND l.status = "active"
                ORDER BY l.created_at DESC
                LIMIT 3
            ');
            $relStmt->execute([
                ':cat_id'  => $listing['category_id'],
                ':curr_id' => $listing['id'],
            ]);
            $related = $relStmt->fetchAll();
        }
    } catch (PDOException $e) {
        error_log('View listing error: ' . $e->getMessage());
    }
}

if (!$listing) {
    echo '
    <div class="container py-5 text-center">
      <div class="card border-0 shadow-sm p-5 max-w-500 mx-auto bg-white rounded-4">
        <i class="bi bi-search fs-1 text-muted mb-3"></i>
        <h3 class="fw-bold">Listing Not Found</h3>
        <p class="text-muted">The listing you requested could not be found or may have been removed.</p>
        <div class="mt-3">
          <a href="' . base_url('listings/') . '" class="btn btn-primary"><i class="bi bi-arrow-left me-1"></i> Back to Listings</a>
        </div>
      </div>
    </div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$pageTitle = $listing['title'];
$isOwner = $currentUser && (int)$currentUser['id'] === (int)$listing['user_id'];
?>

<div class="container py-4">
  <!-- Breadcrumbs -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= base_url('listings/') ?>">Listings</a></li>
      <li class="breadcrumb-item"><a href="<?= base_url('listings/?category=' . urlencode($listing['category_slug'])) ?>"><?= e($listing['category_name']) ?></a></li>
      <li class="breadcrumb-item active text-truncate" style="max-width: 250px;" aria-current="page"><?= e($listing['title']) ?></li>
    </ol>
  </nav>

  <!-- Owner Management Quick Action Bar -->
  <?php if ($isOwner): ?>
    <div class="alert alert-primary d-flex flex-wrap justify-content-between align-items-center shadow-sm mb-4">
      <div class="d-flex align-items-center gap-2 mb-2 mb-sm-0">
        <i class="bi bi-shield-check fs-4"></i>
        <span><strong>You are the author of this listing.</strong> You can edit details or update status.</span>
      </div>
      <div class="d-flex gap-2">
        <a href="<?= base_url('listings/edit.php?id=' . (int)$listing['id']) ?>" class="btn btn-sm btn-primary">
          <i class="bi bi-pencil-square me-1"></i> Edit Ad
        </a>
        <form action="<?= base_url('listings/delete.php') ?>" method="POST" class="d-inline" data-confirm="Are you sure you want to permanently delete this listing?">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$listing['id'] ?>">
          <button type="submit" class="btn btn-sm btn-outline-danger">
            <i class="bi bi-trash me-1"></i> Delete
          </button>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <div class="row g-4">
    <!-- Left Column: Image and Details -->
    <div class="col-lg-8">
      <!-- Image Box -->
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
        <div class="listing-detail-img-box text-center p-2 bg-dark">
          <img src="<?= listing_image_url($listing['image_path']) ?>" alt="<?= e($listing['title']) ?>" class="img-fluid rounded">
        </div>
        <div class="card-body p-4">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <span class="badge bg-primary px-3 py-2 rounded-pill"><?= e($listing['category_name']) ?></span>
            <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i>Posted <?= date('F j, Y', strtotime($listing['created_at'])) ?> (<?= time_ago($listing['created_at']) ?>)</span>
          </div>

          <h1 class="h3 fw-bold mb-3"><?= e($listing['title']) ?></h1>

          <div class="d-flex align-items-center gap-3 mb-4 text-muted small">
            <span><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= e($listing['location']) ?></span>
            <span>•</span>
            <span>Status: <span class="badge bg-<?= $listing['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst(e($listing['status'])) ?></span></span>
          </div>

          <hr class="my-4">

          <h5 class="fw-bold mb-3">Item Description</h5>
          <div class="text-secondary lh-lg" style="white-space: pre-line;">
            <?= e($listing['description']) ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Price and Seller Card -->
    <div class="col-lg-4">
      <!-- Price Card -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white text-center">
        <span class="text-muted small text-uppercase fw-semibold mb-1">Listed Price</span>
        <h2 class="display-6 fw-bold text-success mb-3"><?= format_price($listing['price']) ?></h2>
        <?php if ($listing['status'] === 'sold'): ?>
          <div class="badge bg-danger py-2 fs-6">Item Sold Out</div>
        <?php else: ?>
          <span class="badge bg-success-subtle text-success py-2">Available for Purchase</span>
        <?php endif; ?>
      </div>

      <!-- Seller Contact Card -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
          <i class="bi bi-person-badge text-primary"></i> Seller Information
        </h5>

        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="rounded-circle bg-primary-subtle text-primary p-3 fs-4">
            <i class="bi bi-person"></i>
          </div>
          <div>
            <h6 class="fw-bold mb-0"><?= e($listing['seller_name']) ?></h6>
            <span class="text-muted small">Joined <?= date('M Y', strtotime($listing['seller_joined'])) ?></span>
          </div>
        </div>

        <ul class="list-unstyled small text-secondary mb-4">
          <?php if (!empty($listing['seller_location'])): ?>
            <li class="mb-2"><i class="bi bi-building me-2 text-primary"></i><?= e($listing['seller_location']) ?></li>
          <?php endif; ?>
          <li class="mb-2"><i class="bi bi-envelope me-2 text-primary"></i><?= e($listing['seller_email']) ?></li>
          <?php if (!empty($listing['seller_phone'])): ?>
            <li class="mb-2"><i class="bi bi-telephone me-2 text-primary"></i><?= e($listing['seller_phone']) ?></li>
          <?php endif; ?>
        </ul>

        <div class="d-grid gap-2">
          <?php if (!empty($listing['seller_phone'])): ?>
            <a href="tel:<?= e($listing['seller_phone']) ?>" class="btn btn-success fw-bold">
              <i class="bi bi-telephone-fill me-1"></i> Call Seller
            </a>
          <?php endif; ?>
          <a href="mailto:<?= e($listing['seller_email']) ?>?subject=Inquiry about: <?= rawurlencode($listing['title']) ?>" class="btn btn-outline-primary fw-medium">
            <i class="bi bi-envelope-fill me-1"></i> Send Email
          </a>
        </div>
      </div>

      <!-- Safety Tips -->
      <div class="card border-0 shadow-sm rounded-4 p-4 bg-light">
        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-shield-lock-fill text-warning me-1"></i> Safety Tips</h6>
        <ul class="list-unstyled small text-muted mb-0">
          <li class="mb-2">• Meet in a public, well-lit campus location.</li>
          <li class="mb-2">• Inspect the physical item before payment.</li>
          <li>• Never send electronic deposits in advance.</li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Related Listings in this Category -->
  <?php if (!empty($related)): ?>
    <div class="mt-5 pt-3 border-top">
      <h3 class="h4 fw-bold mb-4">Related in <?= e($listing['category_name']) ?></h3>
      <div class="row g-4">
        <?php foreach ($related as $rel): ?>
          <div class="col-md-4">
            <div class="card listing-card h-100 shadow-sm">
              <div class="listing-img-wrapper">
                <span class="listing-badge-category"><?= e($rel['category_name']) ?></span>
                <img src="<?= listing_image_url($rel['image_path']) ?>" alt="<?= e($rel['title']) ?>" loading="lazy">
              </div>
              <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-baseline mb-2">
                  <span class="listing-price-tag"><?= format_price($rel['price']) ?></span>
                  <span class="text-muted small"><?= time_ago($rel['created_at']) ?></span>
                </div>
                <h5 class="card-title h6 fw-bold mb-2 text-truncate">
                  <a href="<?= base_url('listings/view.php?id=' . (int)$rel['id']) ?>" class="text-dark text-decoration-none">
                    <?= e($rel['title']) ?>
                  </a>
                </h5>
                <a href="<?= base_url('listings/view.php?id=' . (int)$rel['id']) ?>" class="btn btn-sm btn-outline-primary mt-auto">View Item</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

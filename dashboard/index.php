<?php
/**
 * User Dashboard
 * Online Classifieds Marketplace
 */
$pageTitle = 'Seller Dashboard';
require_once __DIR__ . '/../includes/header.php';

// Authentication required
require_auth();

$pdo = get_db_connection();
$myListings = [];
$stats = [
    'total'  => 0,
    'active' => 0,
    'sold'   => 0,
    'value'  => 0.0,
];

if ($pdo) {
    try {
        $stmt = $pdo->prepare('
            SELECT l.*, c.name AS category_name
            FROM listings l
            JOIN categories c ON l.category_id = c.id
            WHERE l.user_id = :user_id
            ORDER BY l.created_at DESC
        ');
        $stmt->execute([':user_id' => $currentUser['id']]);
        $myListings = $stmt->fetchAll();

        foreach ($myListings as $item) {
            $stats['total']++;
            if ($item['status'] === 'active') {
                $stats['active']++;
                $stats['value'] += (float)$item['price'];
            } elseif ($item['status'] === 'sold') {
                $stats['sold']++;
            }
        }
    } catch (PDOException $e) {
        error_log('Dashboard error: ' . $e->getMessage());
    }
}
?>

<div class="container py-4">
  <!-- Profile Header Banner -->
  <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
      <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fs-2 fw-bold" style="width: 64px; height: 64px;">
          <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
        </div>
        <div>
          <h1 class="h4 fw-bold mb-1"><?= e($currentUser['name']) ?></h1>
          <div class="text-muted small d-flex flex-wrap gap-3">
            <span><i class="bi bi-envelope me-1"></i><?= e($currentUser['email']) ?></span>
            <?php if (!empty($currentUser['phone'])): ?>
              <span><i class="bi bi-telephone me-1"></i><?= e($currentUser['phone']) ?></span>
            <?php endif; ?>
            <?php if (!empty($currentUser['location'])): ?>
              <span><i class="bi bi-geo-alt me-1"></i><?= e($currentUser['location']) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div>
        <a href="<?= base_url('listings/create.php') ?>" class="btn btn-warning text-dark fw-bold shadow-sm">
          <i class="bi bi-plus-circle me-1"></i> Post New Advertisement
        </a>
      </div>
    </div>
  </div>

  <!-- Metric Statistics Cards -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small text-uppercase fw-semibold">Total Ads</span>
            <h3 class="fw-bold mb-0 mt-1"><?= $stats['total'] ?></h3>
          </div>
          <div class="stat-icon bg-primary-subtle text-primary">
            <i class="bi bi-collection"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small text-uppercase fw-semibold">Active</span>
            <h3 class="fw-bold mb-0 mt-1 text-success"><?= $stats['active'] ?></h3>
          </div>
          <div class="stat-icon bg-success-subtle text-success">
            <i class="bi bi-check-circle"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small text-uppercase fw-semibold">Sold Items</span>
            <h3 class="fw-bold mb-0 mt-1 text-danger"><?= $stats['sold'] ?></h3>
          </div>
          <div class="stat-icon bg-danger-subtle text-danger">
            <i class="bi bi-bag-check"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small text-uppercase fw-semibold">Active Value</span>
            <h3 class="fw-bold mb-0 mt-1 text-primary"><?= format_price($stats['value']) ?></h3>
          </div>
          <div class="stat-icon bg-info-subtle text-info">
            <i class="bi bi-currency-dollar"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- User Listings Table Card -->
  <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
      <h5 class="h6 fw-bold mb-0"><i class="bi bi-list-task me-2 text-primary"></i>My Posted Advertisements</h5>
      <span class="badge bg-light text-dark border"><?= count($myListings) ?> Items</span>
    </div>

    <?php if (!empty($myListings)): ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th scope="col" style="width: 70px;">Photo</th>
              <th scope="col">Title & Category</th>
              <th scope="col">Price</th>
              <th scope="col">Status</th>
              <th scope="col">Date Posted</th>
              <th scope="col" class="text-end" style="min-width: 170px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($myListings as $item): ?>
              <tr>
                <td>
                  <img src="<?= listing_image_url($item['image_path']) ?>" alt="<?= e($item['title']) ?>" class="rounded" style="width: 60px; height: 45px; object-fit: cover;">
                </td>
                <td>
                  <a href="<?= base_url('listings/view.php?id=' . (int)$item['id']) ?>" class="fw-semibold text-dark text-decoration-none d-block text-truncate" style="max-width: 320px;">
                    <?= e($item['title']) ?>
                  </a>
                  <span class="badge bg-secondary-subtle text-secondary small"><?= e($item['category_name']) ?></span>
                </td>
                <td class="fw-bold text-success">
                  <?= format_price($item['price']) ?>
                </td>
                <td>
                  <?php if ($item['status'] === 'active'): ?>
                    <span class="badge bg-success-subtle text-success">Active</span>
                  <?php elseif ($item['status'] === 'sold'): ?>
                    <span class="badge bg-danger-subtle text-danger">Sold</span>
                  <?php else: ?>
                    <span class="badge bg-secondary-subtle text-secondary">Archived</span>
                  <?php endif; ?>
                </td>
                <td class="small text-muted">
                  <?= date('M j, Y', strtotime($item['created_at'])) ?>
                </td>
                <td class="text-end">
                  <div class="btn-group btn-group-sm">
                    <a href="<?= base_url('listings/view.php?id=' . (int)$item['id']) ?>" class="btn btn-outline-secondary" title="View Ad">
                      <i class="bi bi-eye"></i>
                    </a>
                    <a href="<?= base_url('listings/edit.php?id=' . (int)$item['id']) ?>" class="btn btn-outline-primary" title="Edit Ad">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form action="<?= base_url('listings/delete.php') ?>" method="POST" class="d-inline" data-confirm="Are you sure you want to delete '<?= e($item['title']) ?>'?">
                      <?= csrf_field() ?>
                      <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                      <button type="submit" class="btn btn-outline-danger" title="Delete Ad">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="p-5 text-center">
        <div class="mb-3">
          <i class="bi bi-card-list fs-1 text-muted"></i>
        </div>
        <h5 class="fw-bold">You haven't posted any listings yet</h5>
        <p class="text-muted small mx-auto" style="max-width: 400px;">
          Have books, tech, or furniture you no longer need? Post your first classified advertisement in less than two minutes.
        </p>
        <a href="<?= base_url('listings/create.php') ?>" class="btn btn-primary btn-sm fw-bold">
          <i class="bi bi-plus-circle me-1"></i> Post Your First Ad
        </a>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

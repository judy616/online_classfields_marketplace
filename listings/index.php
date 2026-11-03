<?php
/**
 * Browse Listings Catalog
 * Online Classifieds Marketplace
 */
$pageTitle = 'Browse Listings';
$activeNav = 'listings';
require_once __DIR__ . '/../includes/header.php';

$pdo = get_db_connection();

// Filter parameters from GET request
$searchQuery   = trim($_GET['q'] ?? '');
$categorySlug  = trim($_GET['category'] ?? '');
$minPrice      = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$maxPrice      = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;
$sortBy        = trim($_GET['sort'] ?? 'newest');

$categories = [];
$listings = [];
$totalResults = 0;

if ($pdo) {
    try {
        // Fetch all categories for filter dropdown
        $catStmt = $pdo->query('SELECT * FROM categories ORDER BY name ASC');
        $categories = $catStmt->fetchAll();

        // Build dynamic query with prepared parameters
        $sql = '
            SELECT l.*, c.name AS category_name, c.slug AS category_slug, u.name AS seller_name
            FROM listings l
            JOIN categories c ON l.category_id = c.id
            JOIN users u ON l.user_id = u.id
            WHERE l.status = "active"
        ';
        $params = [];

        // 1. Keyword search (in title, description, or location)
        if (!empty($searchQuery)) {
            $sql .= ' AND (l.title LIKE :q1 OR l.description LIKE :q2 OR l.location LIKE :q3)';
            $wildcard = '%' . $searchQuery . '%';
            $params[':q1'] = $wildcard;
            $params[':q2'] = $wildcard;
            $params[':q3'] = $wildcard;
        }

        // 2. Category filter
        if (!empty($categorySlug)) {
            $sql .= ' AND c.slug = :category_slug';
            $params[':category_slug'] = $categorySlug;
        }

        // 3. Price range filters
        if ($minPrice !== null) {
            $sql .= ' AND l.price >= :min_price';
            $params[':min_price'] = $minPrice;
        }
        if ($maxPrice !== null) {
            $sql .= ' AND l.price <= :max_price';
            $params[':max_price'] = $maxPrice;
        }

        // 4. Sorting
        switch ($sortBy) {
            case 'price_asc':
                $sql .= ' ORDER BY l.price ASC, l.created_at DESC';
                break;
            case 'price_desc':
                $sql .= ' ORDER BY l.price DESC, l.created_at DESC';
                break;
            case 'oldest':
                $sql .= ' ORDER BY l.created_at ASC';
                break;
            case 'newest':
            default:
                $sql .= ' ORDER BY l.created_at DESC';
                break;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $listings = $stmt->fetchAll();
        $totalResults = count($listings);
    } catch (PDOException $e) {
        error_log('Browse listings error: ' . $e->getMessage());
    }
}
?>

<div class="container py-4">
  <!-- Page Header -->
  <div class="row align-items-center mb-4">
    <div class="col-md-7">
      <h1 class="h3 fw-bold mb-1">Browse Classified Ads</h1>
      <p class="text-muted mb-0">
        <?php if (!empty($searchQuery) || !empty($categorySlug)): ?>
          Showing results for filtered search
        <?php else: ?>
          Find verified second-hand items and campus deals
        <?php endif; ?>
      </p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
      <a href="<?= base_url('listings/create.php') ?>" class="btn btn-warning text-dark fw-bold shadow-sm">
        <i class="bi bi-plus-circle me-1"></i> Post Your Advertisement
      </a>
    </div>
  </div>

  <div class="row g-4">
    <!-- Filters Sidebar -->
    <div class="col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="h6 fw-bold mb-0"><i class="bi bi-funnel me-1 text-primary"></i> Filter Search</h5>
          <?php if (!empty($searchQuery) || !empty($categorySlug) || $minPrice !== null || $maxPrice !== null): ?>
            <a href="<?= base_url('listings/') ?>" class="text-danger small text-decoration-none">Reset all</a>
          <?php endif; ?>
        </div>

        <form action="<?= base_url('listings/') ?>" method="GET">
          <!-- Keyword input -->
          <div class="mb-3">
            <label for="q" class="form-label small fw-semibold">Keywords</label>
            <input type="text" name="q" id="q" class="form-control form-control-sm" value="<?= e($searchQuery) ?>" placeholder="e.g. laptop, chair...">
          </div>

          <!-- Category filter -->
          <div class="mb-3">
            <label for="category" class="form-label small fw-semibold">Category</label>
            <select name="category" id="category" class="form-select form-select-sm">
              <option value="">All Categories</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= e($cat['slug']) ?>" <?= $categorySlug === $cat['slug'] ? 'selected' : '' ?>>
                  <?= e($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Price Range -->
          <div class="mb-3">
            <label class="form-label small fw-semibold">Price Range ($)</label>
            <div class="row g-2">
              <div class="col-6">
                <input type="number" step="0.01" name="min_price" class="form-control form-control-sm" placeholder="Min" value="<?= $minPrice !== null ? e((string)$minPrice) : '' ?>">
              </div>
              <div class="col-6">
                <input type="number" step="0.01" name="max_price" class="form-control form-control-sm" placeholder="Max" value="<?= $maxPrice !== null ? e((string)$maxPrice) : '' ?>">
              </div>
            </div>
          </div>

          <!-- Sort Order -->
          <div class="mb-4">
            <label for="sort" class="form-label small fw-semibold">Sort By</label>
            <select name="sort" id="sort" class="form-select form-select-sm">
              <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>Newest First</option>
              <option value="price_asc" <?= $sortBy === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
              <option value="price_desc" <?= $sortBy === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
              <option value="oldest" <?= $sortBy === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
            </select>
          </div>

          <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
            Apply Filters
          </button>
        </form>
      </div>
    </div>

    <!-- Listings Grid -->
    <div class="col-lg-9">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted small">Found <strong><?= (int)$totalResults ?></strong> listing<?= $totalResults === 1 ? '' : 's' ?></span>
      </div>

      <?php if (!empty($listings)): ?>
        <div class="row g-4">
          <?php foreach ($listings as $item): ?>
            <div class="col-md-6 col-lg-4">
              <div class="card listing-card h-100 shadow-sm">
                <div class="listing-img-wrapper">
                  <span class="listing-badge-category"><?= e($item['category_name']) ?></span>
                  <img src="<?= listing_image_url($item['image_path']) ?>" alt="<?= e($item['title']) ?>" loading="lazy">
                </div>
                <div class="card-body d-flex flex-column">
                  <div class="d-flex justify-content-between align-items-baseline mb-2">
                    <span class="listing-price-tag"><?= format_price($item['price']) ?></span>
                    <span class="text-muted small"><i class="bi bi-clock me-1"></i><?= time_ago($item['created_at']) ?></span>
                  </div>
                  <h5 class="card-title h6 fw-bold mb-2 text-truncate" title="<?= e($item['title']) ?>">
                    <a href="<?= base_url('listings/view.php?id=' . (int)$item['id']) ?>" class="text-dark text-decoration-none">
                      <?= e($item['title']) ?>
                    </a>
                  </h5>
                  <p class="text-secondary small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    <?= e($item['description']) ?>
                  </p>
                  <div class="d-flex justify-content-between align-items-center pt-2 border-top text-muted small">
                    <span class="text-truncate" style="max-width: 150px;">
                      <i class="bi bi-geo-alt text-danger me-1"></i><?= e($item['location']) ?>
                    </span>
                    <a href="<?= base_url('listings/view.php?id=' . (int)$item['id']) ?>" class="btn btn-sm btn-outline-primary fw-medium">
                      View Ad
                    </a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
          <div class="mb-3">
            <i class="bi bi-search fs-1 text-muted"></i>
          </div>
          <h4 class="fw-bold">No Matching Listings Found</h4>
          <p class="text-muted mx-auto" style="max-width: 450px;">
            We couldn't find any listings matching your search criteria. Try adjusting keywords, changing categories, or clearing price filters.
          </p>
          <div>
            <a href="<?= base_url('listings/') ?>" class="btn btn-outline-primary">
              Clear All Filters
            </a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

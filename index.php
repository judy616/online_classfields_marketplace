<?php
/**
 * Homepage
 * Online Classifieds Marketplace
 */
$pageTitle = 'Home';
$activeNav = 'home';
require_once __DIR__ . '/includes/header.php';

$pdo = get_db_connection();
$categories = [];
$featuredListings = [];

if ($pdo) {
    try {
        // Fetch categories with listing count
        $catStmt = $pdo->query('
            SELECT c.*, COUNT(l.id) AS listing_count 
            FROM categories c
            LEFT JOIN listings l ON l.category_id = c.id AND l.status = "active"
            GROUP BY c.id
            ORDER BY c.name ASC
        ');
        $categories = $catStmt->fetchAll();

        // Fetch recent active listings
        $listStmt = $pdo->query('
            SELECT l.*, c.name AS category_name, c.slug AS category_slug, u.name AS seller_name
            FROM listings l
            JOIN categories c ON l.category_id = c.id
            JOIN users u ON l.user_id = u.id
            WHERE l.status = "active"
            ORDER BY l.created_at DESC
            LIMIT 6
        ');
        $featuredListings = $listStmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Homepage query error: ' . $e->getMessage());
    }
}
?>

<!-- Hero Banner with Live Search -->
<section class="hero-banner text-center mb-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <span class="badge bg-white text-primary fw-semibold px-3 py-2 mb-3 rounded-pill shadow-sm">
          <i class="bi bi-stars text-warning me-1"></i> Your Trusted Campus & Local Classifieds
        </span>
        <h1 class="display-5 fw-bold mb-3">Buy and Sell Anything, Fast & Easy</h1>
        <p class="lead text-white-50 mb-4">
          Browse verified ads for textbooks, laptops, dorm furniture, vehicles, and student essentials.
        </p>

        <!-- Search Form -->
        <div class="search-box-wrap">
          <form action="<?= base_url('listings/') ?>" method="GET" class="search-input-group row g-1 align-items-center">
            <div class="col-md-6 col-12 d-flex align-items-center px-3">
              <i class="bi bi-search text-muted fs-5 me-2"></i>
              <input type="text" name="q" class="form-control" placeholder="What are you looking for?" aria-label="Search keywords">
            </div>
            <div class="col-md-4 col-8 border-start-md px-2">
              <select name="category" class="form-select text-secondary" aria-label="Filter category">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2 col-4">
              <button type="submit" class="btn btn-warning w-100 fw-bold rounded-pill text-dark py-2">
                Search
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="container">

  <!-- Explore Categories Section -->
  <section class="mb-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
      <div>
        <h2 class="h3 fw-bold mb-1">Explore Categories</h2>
        <p class="text-muted mb-0">Discover thousands of listings categorized for easy browsing</p>
      </div>
      <a href="<?= base_url('listings/') ?>" class="text-primary fw-semibold d-none d-sm-inline">
        View All <i class="bi bi-arrow-right"></i>
      </a>
    </div>

    <div class="row g-3">
      <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $cat): ?>
          <div class="col-6 col-md-4 col-lg-2">
            <a href="<?= base_url('listings/?category=' . urlencode($cat['slug'])) ?>" class="card category-card text-center p-3 text-decoration-none h-100">
              <div class="category-icon-box bg-primary-subtle text-primary mx-auto">
                <i class="bi <?= e($cat['icon'] ?: 'bi-tag') ?>"></i>
              </div>
              <h6 class="fw-bold text-dark mb-1 small"><?= e($cat['name']) ?></h6>
              <span class="text-muted" style="font-size: 0.75rem;"><?= (int)$cat['listing_count'] ?> ads</span>
            </a>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <!-- Category Fallbacks for Pre-DB Demonstration -->
        <?php
        $defaultCats = [
            ['name' => 'Electronics', 'icon' => 'bi-laptop', 'slug' => 'electronics'],
            ['name' => 'Furniture', 'icon' => 'bi-house-door', 'slug' => 'furniture'],
            ['name' => 'Books', 'icon' => 'bi-book', 'slug' => 'books'],
            ['name' => 'Vehicles', 'icon' => 'bi-car-front', 'slug' => 'vehicles'],
            ['name' => 'Fashion', 'icon' => 'bi-handbag', 'slug' => 'fashion'],
            ['name' => 'Mobile Phones', 'icon' => 'bi-phone', 'slug' => 'mobile-phones'],
        ];
        foreach ($defaultCats as $cat): ?>
          <div class="col-6 col-md-4 col-lg-2">
            <a href="<?= base_url('listings/?category=' . urlencode($cat['slug'])) ?>" class="card category-card text-center p-3 text-decoration-none h-100">
              <div class="category-icon-box bg-primary-subtle text-primary mx-auto">
                <i class="bi <?= e($cat['icon']) ?>"></i>
              </div>
              <h6 class="fw-bold text-dark mb-1 small"><?= e($cat['name']) ?></h6>
              <span class="text-muted" style="font-size: 0.75rem;">Browse</span>
            </a>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

  <!-- Featured Listings Section -->
  <section class="mb-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
      <div>
        <h2 class="h3 fw-bold mb-1">Recent Listings</h2>
        <p class="text-muted mb-0">Handpicked items recently listed by sellers in your community</p>
      </div>
      <a href="<?= base_url('listings/') ?>" class="btn btn-outline-primary btn-sm fw-semibold">
        See All Ads <i class="bi bi-chevron-right ms-1"></i>
      </a>
    </div>

    <div class="row g-4">
      <?php if (!empty($featuredListings)): ?>
        <?php foreach ($featuredListings as $item): ?>
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
                  <span class="text-truncate" style="max-width: 170px;">
                    <i class="bi bi-geo-alt text-danger me-1"></i><?= e($item['location']) ?>
                  </span>
                  <a href="<?= base_url('listings/view.php?id=' . (int)$item['id']) ?>" class="btn btn-sm btn-outline-primary fw-medium">
                    Details
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12">
          <div class="card border-dashed p-5 text-center bg-white shadow-sm">
            <div class="mb-3">
              <i class="bi bi-inbox fs-1 text-muted"></i>
            </div>
            <h4 class="fw-bold">No Listings Found Yet</h4>
            <p class="text-muted mx-auto" style="max-width: 480px;">
              Once the database is set up and sample data is seeded, your marketplace listings will automatically appear here.
            </p>
            <div class="d-flex justify-content-center gap-2">
              <a href="<?= base_url('listings/create.php') ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Create First Listing
              </a>
              <a href="<?= base_url('listings/') ?>" class="btn btn-outline-secondary">
                Browse Marketplace
              </a>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- How It Works Section -->
  <section class="py-5 mb-5 bg-white rounded-4 border shadow-sm p-4 p-md-5">
    <div class="text-center mb-5">
      <h2 class="h3 fw-bold mb-2">How ClassiMarket Works</h2>
      <p class="text-muted">A simple, transparent 3-step marketplace for everyone</p>
    </div>
    <div class="row g-4 text-center">
      <div class="col-md-4">
        <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex p-3 mb-3">
          <i class="bi bi-camera fs-3"></i>
        </div>
        <h5 class="fw-bold">1. Post Your Ad</h5>
        <p class="text-muted small">Snap photos of your item, set your price, and describe what makes it special in minutes.</p>
      </div>
      <div class="col-md-4">
        <div class="rounded-circle bg-success-subtle text-success d-inline-flex p-3 mb-3">
          <i class="bi bi-chat-dots fs-3"></i>
        </div>
        <h5 class="fw-bold">2. Connect With Buyers</h5>
        <p class="text-muted small">Direct communication between sellers and verified local buyers via phone or campus meetup.</p>
      </div>
      <div class="col-md-4">
        <div class="rounded-circle bg-warning-subtle text-warning d-inline-flex p-3 mb-3">
          <i class="bi bi-shield-check fs-3"></i>
        </div>
        <h5 class="fw-bold">3. Deal Completed</h5>
        <p class="text-muted small">Meet safely in public spots, inspect the item, exchange payment, and mark the ad as sold.</p>
      </div>
    </div>
  </section>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

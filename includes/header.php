<?php
/**
 * Header Partial
 * Online Classifieds Marketplace
 */
require_once __DIR__ . '/functions.php';

$currentUser = current_user();
$flash = get_flash();
$dbConnected = is_db_connected();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?>ClassiMarket | Online Classifieds</title>
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Custom Marketplace Styles -->
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

  <!-- Main Navigation -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= base_url() ?>">
        <i class="bi bi-shop fs-4 text-warning"></i>
        <span>ClassiMarket</span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#marketplaceNavbar" aria-controls="marketplaceNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="marketplaceNavbar">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link <?= empty($activeNav) || $activeNav === 'home' ? 'active fw-semibold' : '' ?>" href="<?= base_url() ?>">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= isset($activeNav) && $activeNav === 'listings' ? 'active fw-semibold' : '' ?>" href="<?= base_url('listings/') ?>">Browse Ads</a>
          </li>
        </ul>

        <div class="d-flex align-items-center gap-2 flex-wrap">
          <?php if ($currentUser): ?>
            <a href="<?= base_url('listings/create.php') ?>" class="btn btn-warning text-dark fw-semibold shadow-sm">
              <i class="bi bi-plus-circle me-1"></i> Post Ad
            </a>

            <div class="dropdown">
              <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle fs-5"></i>
                <span class="d-none d-sm-inline"><?= e($currentUser['name']) ?></span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><h6 class="dropdown-header">Signed in as <strong><?= e($currentUser['email']) ?></strong></h6></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2" href="<?= base_url('dashboard/') ?>">
                    <i class="bi bi-speedometer2 text-primary"></i> My Dashboard
                  </a>
                </li>
                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2" href="<?= base_url('listings/create.php') ?>">
                    <i class="bi bi-plus-square text-success"></i> Post New Listing
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2 text-danger" href="<?= base_url('auth/logout.php') ?>">
                    <i class="bi bi-box-arrow-right"></i> Log Out
                  </a>
                </li>
              </ul>
            </div>
          <?php else: ?>
            <a href="<?= base_url('auth/login.php') ?>" class="btn btn-outline-light">
              <i class="bi bi-box-arrow-in-right me-1"></i> Log In
            </a>
            <a href="<?= base_url('auth/register.php') ?>" class="btn btn-light fw-medium">
              <i class="bi bi-person-plus me-1"></i> Register
            </a>
            <a href="<?= base_url('listings/create.php') ?>" class="btn btn-warning text-dark fw-semibold shadow-sm">
              <i class="bi bi-plus-circle me-1"></i> Post Ad
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>

  <!-- Database Connection Notice (Graceful fallback) -->
  <?php if (!$dbConnected): ?>
    <div class="alert alert-warning border-0 rounded-0 mb-0 py-2 text-center shadow-sm">
      <div class="container d-flex align-items-center justify-content-center gap-2">
        <i class="bi bi-exclamation-triangle-fill fs-5 text-warning"></i>
        <span><strong>Database Setup Mode:</strong> MySQL is not connected yet (Port <?= e((string)DB_PORT) ?>). Please see setup instructions to start/configure your database.</span>
      </div>
    </div>
  <?php endif; ?>

  <!-- Flash Notification Container -->
  <?php if ($flash): ?>
    <div class="container mt-3">
      <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle-fill' : 'info-circle-fill' ?> me-2"></i>
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    </div>
  <?php endif; ?>

  <!-- Main Content Area Starts -->
  <main class="flex-grow-1">

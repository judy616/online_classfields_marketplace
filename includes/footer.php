<?php
/**
 * Footer Partial
 * Online Classifieds Marketplace
 */
?>
  </main>
  <!-- Main Content Ends -->

  <!-- Marketplace Footer -->
  <footer class="bg-dark text-white pt-5 pb-4 mt-5 border-top">
    <div class="container">
      <div class="row g-4">
        <!-- Col 1: About -->
        <div class="col-lg-4 col-md-6">
          <div class="d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-shop fs-3 text-warning"></i>
            <h5 class="mb-0 fw-bold">ClassiMarket</h5>
          </div>
          <p class="text-secondary small">
            A secure, modern, full-featured college web development project. Built using procedural PHP, PDO prepared statements, MySQL, and responsive Bootstrap 5.
          </p>
          <div class="d-flex gap-2">
            <span class="badge bg-primary">PHP 8</span>
            <span class="badge bg-info text-dark">MySQL</span>
            <span class="badge bg-secondary">Bootstrap 5</span>
            <span class="badge bg-success">PDO Secure</span>
          </div>
        </div>

        <!-- Col 2: Quick Links -->
        <div class="col-lg-2 col-md-6">
          <h6 class="fw-bold text-uppercase text-warning mb-3">Explore</h6>
          <ul class="list-unstyled small">
            <li class="mb-2"><a href="<?= base_url() ?>" class="text-secondary text-decoration-none hover-white">Home</a></li>
            <li class="mb-2"><a href="<?= base_url('listings/') ?>" class="text-secondary text-decoration-none hover-white">Browse Listings</a></li>
            <li class="mb-2"><a href="<?= base_url('listings/create.php') ?>" class="text-secondary text-decoration-none hover-white">Post an Advertisement</a></li>
            <li class="mb-2"><a href="<?= base_url('dashboard/') ?>" class="text-secondary text-decoration-none hover-white">Seller Dashboard</a></li>
          </ul>
        </div>

        <!-- Col 3: Popular Categories -->
        <div class="col-lg-3 col-md-6">
          <h6 class="fw-bold text-uppercase text-warning mb-3">Top Categories</h6>
          <ul class="list-unstyled small">
            <li class="mb-2"><a href="<?= base_url('listings/?category=electronics') ?>" class="text-secondary text-decoration-none hover-white"><i class="bi bi-laptop me-2"></i>Electronics</a></li>
            <li class="mb-2"><a href="<?= base_url('listings/?category=furniture') ?>" class="text-secondary text-decoration-none hover-white"><i class="bi bi-house-door me-2"></i>Furniture</a></li>
            <li class="mb-2"><a href="<?= base_url('listings/?category=books') ?>" class="text-secondary text-decoration-none hover-white"><i class="bi bi-book me-2"></i>Textbooks & Notes</a></li>
            <li class="mb-2"><a href="<?= base_url('listings/?category=vehicles') ?>" class="text-secondary text-decoration-none hover-white"><i class="bi bi-car-front me-2"></i>Vehicles & Bikes</a></li>
          </ul>
        </div>

        <!-- Col 4: Academic Notes -->
        <div class="col-lg-3 col-md-6">
          <h6 class="fw-bold text-uppercase text-warning mb-3">Project Specs</h6>
          <p class="text-secondary small mb-2">
            <strong>Architecture:</strong> Procedural PHP with modular includes & sanitized forms.
          </p>
          <p class="text-secondary small mb-2">
            <strong>Security:</strong> CSRF tokens, session regeneration, Bcrypt hashes, MIME verification.
          </p>
          <p class="text-secondary small mb-0">
            <strong>Target:</strong> College Viva & Web Development Lab Project.
          </p>
        </div>
      </div>

      <hr class="border-secondary my-4">

      <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center text-secondary small">
        <p class="mb-2 mb-sm-0">&copy; <?= date('Y') ?> Online Classifieds Marketplace. All rights reserved.</p>
        <p class="mb-0">Designed for College Web Development Project</p>
      </div>
    </div>
  </footer>

  <!-- Bootstrap 5 Bundle JS (Includes Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <!-- Custom JavaScript -->
  <script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>

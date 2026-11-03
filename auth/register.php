<?php
/**
 * User Registration
 * Online Classifieds Marketplace
 */
$pageTitle = 'Create Account';
require_once __DIR__ . '/../includes/header.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ' . base_url('dashboard/'));
    exit;
}

$errors = [];
$name = '';
$email = '';
$phone = '';
$location = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid or expired session token. Please try submitting again.';
    }

    // 2. Collect and sanitize input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $location = trim($_POST['location'] ?? '');

    // 3. Validation checks
    if (empty($name) || strlen($name) < 2) {
        $errors[] = 'Full name is required (minimum 2 characters).';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }

    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    // 4. Check for duplicate email and save to DB
    $pdo = get_db_connection();
    if (!$pdo) {
        $errors[] = 'Database connection is currently unavailable. Please verify your MySQL server.';
    } elseif (empty($errors)) {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $errors[] = 'An account with this email address already exists. Please log in.';
            } else {
                // Hash password securely with Bcrypt
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $insertStmt = $pdo->prepare('
                    INSERT INTO users (name, email, password, phone, location, created_at)
                    VALUES (:name, :email, :password, :phone, :location, NOW())
                ');
                $insertStmt->execute([
                    ':name'     => $name,
                    ':email'    => $email,
                    ':password' => $hashedPassword,
                    ':phone'    => $phone ?: null,
                    ':location' => $location ?: null,
                ]);

                $newUserId = (int)$pdo->lastInsertId();

                // Automatically log the new user in
                login_user([
                    'id'       => $newUserId,
                    'name'     => $name,
                    'email'    => $email,
                    'phone'    => $phone,
                    'location' => $location,
                ]);

                set_flash('success', 'Account created successfully! Welcome to ClassiMarket.');
                header('Location: ' . base_url('dashboard/'));
                exit;
            }
        } catch (PDOException $e) {
            error_log('Registration error: ' . $e->getMessage());
            $errors[] = 'An unexpected database error occurred. Please try again.';
        }
    }
}
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
      <div class="card auth-card shadow-sm p-4 p-md-5">
        <div class="text-center mb-4">
          <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex p-3 mb-2">
            <i class="bi bi-person-plus fs-3"></i>
          </div>
          <h2 class="h4 fw-bold">Create an Account</h2>
          <p class="text-muted small">Join thousands of students and locals buying and selling today</p>
        </div>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger shadow-sm">
            <ul class="mb-0 ps-3">
              <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form action="<?= base_url('auth/register.php') ?>" method="POST" novalidate>
          <?= csrf_field() ?>

          <div class="mb-3">
            <label for="name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person"></i></span>
              <input type="text" name="name" id="name" class="form-control" value="<?= e($name) ?>" placeholder="e.g. Alex Johnson" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-envelope"></i></span>
              <input type="email" name="email" id="email" class="form-control" value="<?= e($email) ?>" placeholder="e.g. alex@example.com" required>
            </div>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-sm-6">
              <label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" id="password" class="form-control" placeholder="Min. 6 chars" required>
              </div>
            </div>
            <div class="col-sm-6">
              <label for="confirm_password" class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Repeat password" required>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="phone" class="form-label fw-semibold">Phone Number <span class="text-muted small">(Optional)</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-telephone"></i></span>
              <input type="tel" name="phone" id="phone" class="form-control" value="<?= e($phone) ?>" placeholder="e.g. +1 (555) 019-2834">
            </div>
            <div class="form-text">Shared with potential buyers when they view your ads.</div>
          </div>

          <div class="mb-4">
            <label for="location" class="form-label fw-semibold">Location / Campus <span class="text-muted small">(Optional)</span></label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
              <input type="text" name="location" id="location" class="form-control" value="<?= e($location) ?>" placeholder="e.g. North Hall, Student Union">
            </div>
          </div>

          <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mb-3">
            Register Account
          </button>

          <div class="text-center text-muted small">
            Already have an account? 
            <a href="<?= base_url('auth/login.php') ?>" class="text-primary fw-semibold">Log in here</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
/**
 * User Login
 * Online Classifieds Marketplace
 */
$pageTitle = 'Log In';
require_once __DIR__ . '/../includes/header.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ' . base_url('dashboard/'));
    exit;
}

$errors = [];
$email = '';
$redirect = $_GET['redirect'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid or expired session token. Please try submitting again.';
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = $_POST['redirect'] ?? '';

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($password)) {
        $errors[] = 'Please enter your password.';
    }

    $pdo = get_db_connection();
    if (!$pdo) {
        $errors[] = 'Database connection is currently unavailable. Please verify MySQL server status.';
    } elseif (empty($errors)) {
        try {
            // Prepared statement to retrieve user
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Successful authentication
                login_user($user);
                set_flash('success', 'Welcome back, ' . $user['name'] . '!');

                $target = !empty($redirect) && strpos($redirect, '/') === 0 ? $redirect : base_url('dashboard/');
                header('Location: ' . $target);
                exit;
            } else {
                $errors[] = 'Invalid email address or password.';
            }
        } catch (PDOException $e) {
            error_log('Login error: ' . $e->getMessage());
            $errors[] = 'A server error occurred during login. Please try again.';
        }
    }
}
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
      <div class="card auth-card shadow-sm p-4 p-md-5">
        <div class="text-center mb-4">
          <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex p-3 mb-2">
            <i class="bi bi-box-arrow-in-right fs-3"></i>
          </div>
          <h2 class="h4 fw-bold">Sign In to Your Account</h2>
          <p class="text-muted small">Manage your active listings, view inquiries, and post new ads</p>
        </div>

        <!-- College Demo Helper Callout -->
        <div class="alert alert-info py-2 small shadow-sm mb-3">
          <i class="bi bi-info-circle me-1"></i>
          <strong>Demo Credentials:</strong><br>
          Email: <code>alex@example.com</code><br>
          Password: <code>password123</code>
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

        <form action="<?= base_url('auth/login.php') ?>" method="POST" novalidate>
          <?= csrf_field() ?>
          <input type="hidden" name="redirect" value="<?= e($redirect) ?>">

          <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Email Address</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-envelope"></i></span>
              <input type="email" name="email" id="email" class="form-control" value="<?= e($email) ?>" placeholder="e.g. alex@example.com" required autofocus>
            </div>
          </div>

          <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
              <label for="password" class="form-label fw-semibold mb-0">Password</label>
            </div>
            <div class="input-group mt-1">
              <span class="input-group-text"><i class="bi bi-lock"></i></span>
              <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
            </div>
          </div>

          <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mb-3">
            Sign In
          </button>

          <div class="text-center text-muted small">
            Don't have an account yet? 
            <a href="<?= base_url('auth/register.php') ?>" class="text-primary fw-semibold">Create an account</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
/**
 * Logout Handler
 * Online Classifieds Marketplace
 */
require_once __DIR__ . '/../includes/functions.php';

logout_user();
set_flash('info', 'You have been logged out successfully.');
header('Location: ' . base_url('auth/login.php'));
exit;

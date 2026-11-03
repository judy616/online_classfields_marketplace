-- ============================================================
-- Seed Data: Online Classifieds Marketplace
-- ============================================================

USE `online_classifieds`;

-- ------------------------------------------------------------
-- Categories
-- ------------------------------------------------------------
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`) VALUES
(1, 'Electronics', 'electronics', 'bi-laptop', 'Laptops, TVs, audio systems, and gadget accessories'),
(2, 'Furniture', 'furniture', 'bi-house-door', 'Couches, study tables, beds, and office chairs'),
(3, 'Books', 'books', 'bi-book', 'College textbooks, novels, competitive exam prep, and guides'),
(4, 'Vehicles', 'vehicles', 'bi-car-front', 'Bicycles, motorcycles, scooters, and cars'),
(5, 'Fashion', 'fashion', 'bi-handbag', 'Clothing, footwear, watches, and personal accessories'),
(6, 'Mobile Phones', 'mobile-phones', 'bi-phone', 'Smartphones, tablets, and mobile accessories')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `slug`=VALUES(`slug`), `icon`=VALUES(`icon`);

-- ------------------------------------------------------------
-- Demo Users (Password for all demo users is: password123)
-- Hash generated via password_hash('password123', PASSWORD_BCRYPT)
-- ------------------------------------------------------------
INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `location`, `created_at`) VALUES
(1, 'Alex Johnson', 'alex@example.com', '$2y$10$uFd5MIn7jofTg8dL6FE6jOWQTKfa0CaSrj8befnbUd4xHZpVfvj1S', '+1 (555) 234-5678', 'Downtown Campus', NOW()),
(2, 'Sarah Williams', 'sarah@example.com', '$2y$10$uFd5MIn7jofTg8dL6FE6jOWQTKfa0CaSrj8befnbUd4xHZpVfvj1S', '+1 (555) 876-5432', 'North Hall, Block B', NOW())
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- ------------------------------------------------------------
-- Demo Listings
-- ------------------------------------------------------------
INSERT INTO `listings` (`id`, `user_id`, `category_id`, `title`, `description`, `price`, `location`, `image_path`, `status`, `created_at`) VALUES
(1, 1, 1, 'MacBook Air M1 256GB - Excellent Condition', 'Selling my MacBook Air M1 (Space Gray, 8GB RAM, 256GB SSD). Battery health 92%. Original 30W charger and box included. Ideal for computer science students and light video editing.', 550.00, 'Downtown Campus, Library entrance', 'sample-laptop.svg', 'active', NOW()),
(2, 2, 2, 'Ergonomic Mesh Study Chair with Lumbar Support', 'Comfortable high-back swivel chair with breathable mesh and adjustable armrests. Used for only 6 months during finals. Perfect for dorm study setups.', 65.00, 'North Hall, Block B', 'sample-chair.svg', 'active', NOW()),
(3, 1, 3, 'Calculus Early Transcendentals (8th Edition) & Notes', 'Hardcover edition in pristine condition with no highlighter markings. Will include handwritten revision flashcards and solved problem sheets for free.', 40.00, 'Downtown Campus', 'sample-book.svg', 'active', NOW()),
(4, 2, 4, 'Trek 21-Speed Mountain Bike + U-Lock Included', 'Smooth aluminum frame commuter bike. Front suspension, new brake pads, and puncture-resistant tires installed last month. Heavy-duty Kryptonite U-lock included.', 180.00, 'Campus West Bike Sheds', 'sample-bike.svg', 'active', NOW()),
(5, 1, 6, 'iPhone 13 128GB Midnight - Unlocked', 'Factory unlocked iPhone 13 in midnight black. Screen protector installed from day one. Includes original charging cable and two Spigen cases.', 380.00, 'Student Union Center', 'sample-phone.svg', 'active', NOW()),
(6, 2, 5, 'Fossil Minimalist Chronograph Leather Watch', 'Authentic Fossil stainless steel watch with genuine dark brown leather strap. Water resistant 50m. Brand new battery installed last week.', 55.00, 'North Hall, Block B', 'sample-watch.svg', 'active', NOW())
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`);

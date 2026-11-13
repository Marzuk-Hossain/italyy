-- ========================================================
-- ITALY OPTICS - MySQL Database Schema
-- 12 Jamal Khan Road, Chattogram 4000
-- ========================================================

CREATE DATABASE IF NOT EXISTS `italy_optics` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `italy_optics`;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(30) NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('customer', 'admin') DEFAULT 'customer',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` VARCHAR(255) NULL,
  `display_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Products Table
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(160) NOT NULL UNIQUE,
  `brand` VARCHAR(100) NOT NULL DEFAULT 'Italy Optics Heritage',
  `category_id` INT UNSIGNED NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `original_price` DECIMAL(10,2) NULL,
  `rating` DECIMAL(2,1) DEFAULT 5.0,
  `reviews_count` INT UNSIGNED DEFAULT 1,
  `image` LONGTEXT NOT NULL,
  `description` TEXT NULL,
  `features` TEXT NULL,
  `gender` VARCHAR(50) DEFAULT 'Unisex',
  `shape` VARCHAR(50) DEFAULT 'Classic',
  `in_stock` TINYINT(1) DEFAULT 1,
  `is_featured` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Product Colors Table
CREATE TABLE IF NOT EXISTS `product_colors` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `color_name` VARCHAR(80) NOT NULL,
  `color_hex` VARCHAR(20) NOT NULL,
  `is_default` TINYINT(1) DEFAULT 0,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Orders Table
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` INT UNSIGNED NULL,
  `customer_name` VARCHAR(120) NOT NULL,
  `customer_phone` VARCHAR(30) NOT NULL,
  `customer_email` VARCHAR(150) NOT NULL,
  `delivery_type` VARCHAR(50) DEFAULT 'home',
  `delivery_address` TEXT NOT NULL,
  `payment_method` VARCHAR(50) DEFAULT 'cod',
  `shipping_fee` DECIMAL(10,2) DEFAULT 0.00,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `status` ENUM('pending', 'confirmed', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Order Items Table
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NULL,
  `product_name` VARCHAR(150) NOT NULL,
  `color_name` VARCHAR(80) NOT NULL,
  `color_hex` VARCHAR(20) NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `prescription_note` TEXT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Reviews Table
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `reviewer_name` VARCHAR(120) NOT NULL,
  `location` VARCHAR(120) DEFAULT 'Chattogram',
  `rating` INT NOT NULL DEFAULT 5,
  `review_text` TEXT NOT NULL,
  `is_verified` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Contact Messages Table
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `subject` VARCHAR(150) NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('new', 'read', 'replied') DEFAULT 'new',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================
-- SEED DATA
-- ========================================================

-- Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `display_order`) VALUES
(1, 'Eyeglasses', 'eyeglasses', 'Prescription & luxury Italian frames', 1),
(2, 'Sunglasses', 'sunglasses', 'Polarized UV400 protective shades', 2),
(3, 'Blue Light', 'blue-light', 'Digital screen eyestrain defense', 3),
(4, 'Reading Glasses', 'reading-glasses', 'High clarity executive readers', 4),
(5, 'Contact Lenses', 'contact-lenses', 'Hydrogel & breathable daily/monthly lenses', 5),
(6, 'Kids', 'kids', 'Flexible & shatterproof children frames', 6)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Default Users:
-- Admin: admin@italyoptics.com / admin123
-- Customer: customer@italyoptics.com / customer123
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password_hash`, `role`) VALUES
(1, 'Store Manager (Admin)', 'admin@italyoptics.com', '01708-541674', '$2y$10$w855.j70qT1E0Jsl.X8C1.FzF0Z7jZ8F.2o5uE598o9aXjQhHhW2e', 'admin'),
(2, 'Tanvir Chowdhury', 'customer@italyoptics.com', '01819-556677', '$2y$10$w855.j70qT1E0Jsl.X8C1.FzF0Z7jZ8F.2o5uE598o9aXjQhHhW2e', 'customer')
ON DUPLICATE KEY UPDATE `email`=VALUES(`email`);

-- Products
INSERT INTO `products` (`id`, `name`, `slug`, `brand`, `category_id`, `price`, `original_price`, `rating`, `reviews_count`, `image`, `description`, `features`, `gender`, `shape`, `in_stock`, `is_featured`) VALUES
(1, 'Milano Elegance Titanium', 'milano-elegance-titanium', 'Italy Optics Heritage', 1, 3850.00, 4800.00, 5.0, 18, 'https://images.unsplash.com/photo-1591076482161-42ce6da69f68?auto=format&fit=crop&w=800&q=80', 'Handcrafted ultra-lightweight titanium frame inspired by Milanese architecture. Engineered for featherlight comfort all day in Chittagong.', 'Ultra-light Titanium (14g)|Anti-reflective ready|Silicone air-cushion nose pads|2-Year Warranty', 'Unisex', 'Round', 1, 1),
(2, 'Venezia Riviera Polarized', 'venezia-riviera-polarized', 'Venezia Eyewear', 2, 4600.00, 5500.00, 4.9, 24, 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=800&q=80', 'Italian biological acetate sunglasses with UV400 polarized precision lenses. Eliminates harsh glare across Chattogram sunny outdoors.', '100% UV400 Polarized|Mazzucchelli Bio-Acetate|5-Barrel Metal Hinges|Signature Hard Case', 'Men', 'Wayfarer', 1, 1),
(3, 'Firenze BlueShield Pro', 'firenze-blueshield-pro', 'Italy Optics Tech', 3, 2950.00, 3500.00, 5.0, 31, 'https://images.unsplash.com/photo-1577803645773-f96470509666?auto=format&fit=crop&w=800&q=80', 'Engineered for developers, students and digital professionals. Blocks 95% of harmful 410-450nm blue light without color distortion.', 'Anti-Blue Light Filter|Hydrophobic Easy-Clean Coating|TR90 Memory Polymer|Zero Eyestrain Guarantee', 'Unisex', 'Square', 1, 1),
(4, 'Roma Cat-Eye Silhouette', 'roma-cat-eye-silhouette', 'Bella Donna Italia', 1, 3400.00, 4200.00, 5.0, 15, 'https://images.unsplash.com/photo-1508296695146-257a814070b4?auto=format&fit=crop&w=800&q=80', 'Bold uplifting Italian cat-eye design that sculpts facial contours with subtle monochrome luxury. Hand-polished edges.', 'Flattering Lifted Silhouette|High-grade Cellulose Acetate|Spring Hinge Temples|Free Leather Case', 'Women', 'Cat-Eye', 1, 0),
(5, 'Torino Aviator Classic 1978', 'torino-aviator-classic-1978', 'Italy Optics Heritage', 2, 4900.00, 6200.00, 4.8, 19, 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?auto=format&fit=crop&w=800&q=80', 'Timeless double-bridge teardrop frame fabricated from surgical stainless steel. Finished in obsidian black and titanium.', 'Corrosion-resistant Alloy|UV400 Mineral Glass|Adjustable Silicone Nose Guards|Laser-etched Logo', 'Unisex', 'Aviator', 1, 1),
(6, 'Aquavision HydraComfort Lenses', 'aquavision-hydracomfort-lenses', 'OptiHydro Italia', 5, 1850.00, 2200.00, 5.0, 22, 'https://images.unsplash.com/photo-1587854692152-cbe660dbde88?auto=format&fit=crop&w=800&q=80', 'Breathable monthly silicone hydrogel contact lenses with moisture retention matrix. Keeps eyes moist in air-conditioned and humid conditions.', '6 Lenses per Pack|58% High Water Content|UV-B Blocking Capability|Silicone Hydrogel Material', 'Unisex', 'Spherical', 1, 0),
(7, 'Bambino Flex Unbreakable Kids', 'bambino-flex-unbreakable-kids', 'Italy Optics Kids', 6, 2200.00, 2700.00, 5.0, 12, 'https://images.unsplash.com/photo-1509695507497-903c140c43b0?auto=format&fit=crop&w=800&q=80', 'Virtually indestructible, bendable, non-toxic rubberized frames engineered for active school-going children in Chittagong.', '180-degree Bendable Temples|BPA-Free Non-Toxic Material|Includes Elastic Retention Strap|Drop-proof Guaranteed', 'Kids', 'Round', 1, 0),
(8, 'Palermo Executive Half-Rim', 'palermo-executive-half-rim', 'Italy Optics Heritage', 4, 3100.00, 3800.00, 4.9, 16, 'https://images.unsplash.com/photo-1574258495973-f010dfbb5371?auto=format&fit=crop&w=800&q=80', 'Sophisticated semi-rimless frame designed for reading and boardroom authority. Lightweight browline structure with precision spring hinges.', 'Precision Semi-Rimless|Spring Loaded Temples|Accommodates Progressive Lenses|Ultralight Comfort', 'Men', 'Rectangle', 1, 0)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Product Colors
INSERT INTO `product_colors` (`product_id`, `color_name`, `color_hex`, `is_default`) VALUES
-- Milano Titanium
(1, 'Matte Black', '#000000', 1),
(1, 'Silver Chrome', '#E0E0E0', 0),
(1, 'Gunmetal Slate', '#4A4A4A', 0),
(1, 'Pure White', '#FFFFFF', 0),
-- Venezia Riviera
(2, 'Obsidian Black', '#0F0F0F', 1),
(2, 'Havana Brown', '#3D2817', 0),
(2, 'Smoke Gray', '#666666', 0),
-- Firenze BlueShield
(3, 'Crystal Clear', '#F0F0F0', 1),
(3, 'Jet Black', '#000000', 0),
(3, 'Frosted White', '#E8E8E8', 0),
-- Roma Cat-Eye
(4, 'Gloss Black', '#000000', 1),
(4, 'Pearl White', '#F7F7F7', 0),
(4, 'Dark Tortoise', '#2B1E16', 0),
-- Torino Aviator
(5, 'Matte Black', '#000000', 1),
(5, 'Silver Mirror', '#CCCCCC', 0),
-- Contact Lenses
(6, 'Natural Clear', '#E6F0FA', 1),
(6, 'Ocean Tint', '#90B4CE', 0),
-- Kids
(7, 'Onyx Black', '#111111', 1),
(7, 'Chalk White', '#F4F4F4', 0),
(7, 'Cobalt Blue', '#1E3A8A', 0),
-- Palermo Executive
(8, 'Brushed Gunmetal', '#333333', 1),
(8, 'Polished Black', '#000000', 0),
(8, 'Titanium Gray', '#777777', 0);

-- Reviews (Italy Optics 5.0, 3 Google reviews)
INSERT INTO `reviews` (`id`, `reviewer_name`, `location`, `rating`, `review_text`, `is_verified`) VALUES
(1, 'Tanvir Ahmed', 'Jamal Khan, Chattogram', 5, 'Best optical shop in Jamal Khan! The owner took time to test my eyes and helped me pick the right Italian titanium frame. Prices are very reasonable and service is top notch.', 1),
(2, 'Dr. Nusrat Jahan', 'Chittagong Medical College Area', 5, 'Got computer blue light glasses and prescription sunglasses from Italy Optics. Lens precision is 100% accurate. Highly recommend anyone in Chittagong looking for authentic frames.', 1),
(3, 'Zubair Hossain', 'Chattogram', 5, 'Excellent collection and 5-star customer hospitality! They delivered my customized lenses within 24 hours. The frame fits perfectly.', 1)
ON DUPLICATE KEY UPDATE `reviewer_name`=VALUES(`reviewer_name`);

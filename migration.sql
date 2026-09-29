-- ============================================================
-- KHODIYAR COMPUTER - Database Migration (v2.0)
-- Adds: Payments, Testimonials, Blog, Newsletter, Enhanced Bookings
-- ============================================================

USE `khodiyar_computer`;

-- -------------------------------------------
-- 7. Payments Table
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `payment_id` VARCHAR(30) NOT NULL UNIQUE,
    `booking_id` VARCHAR(20) NOT NULL,
    `user_id` INT(11) DEFAULT NULL,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_email` VARCHAR(100) NOT NULL,
    `customer_phone` VARCHAR(20) NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `payment_method` ENUM('card', 'net_banking', 'upi', 'cash') NOT NULL DEFAULT 'cash',
    `card_last_four` VARCHAR(4) DEFAULT NULL,
    `card_type` VARCHAR(20) DEFAULT NULL,
    `bank_name` VARCHAR(100) DEFAULT NULL,
    `upi_id` VARCHAR(100) DEFAULT NULL,
    `transaction_ref` VARCHAR(100) DEFAULT NULL,
    `payment_status` ENUM('pending', 'paid', 'failed', 'refunded', 'cancelled') NOT NULL DEFAULT 'pending',
    `payment_date` TIMESTAMP NULL DEFAULT NULL,
    `payment_response` TEXT DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_payment_id` (`payment_id`),
    INDEX `idx_booking_id` (`booking_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_payment_status` (`payment_status`),
    INDEX `idx_payment_method` (`payment_method`),
    CONSTRAINT `fk_payment_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`booking_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_payment_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- 8. Testimonials Table
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `testimonials` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `client_name` VARCHAR(100) NOT NULL,
    `client_designation` VARCHAR(100) DEFAULT NULL,
    `client_company` VARCHAR(100) DEFAULT NULL,
    `client_image` VARCHAR(255) DEFAULT NULL,
    `rating` TINYINT(1) NOT NULL DEFAULT 5,
    `testimonial_text` TEXT NOT NULL,
    `service_id` INT(11) DEFAULT NULL,
    `display_order` INT(3) NOT NULL DEFAULT 0,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_testimonial_status` (`status`),
    INDEX `idx_testimonial_order` (`display_order`),
    INDEX `idx_testimonial_service` (`service_id`),
    CONSTRAINT `fk_testimonial_service` FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- 9. Blog Posts Table
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_posts` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(200) NOT NULL UNIQUE,
    `category` VARCHAR(100) DEFAULT 'General',
    `tags` VARCHAR(255) DEFAULT NULL,
    `excerpt` TEXT DEFAULT NULL,
    `content` LONGTEXT NOT NULL,
    `featured_image` VARCHAR(255) DEFAULT NULL,
    `author` VARCHAR(100) DEFAULT 'Khodiyar Computer',
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `status` ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    `published_at` TIMESTAMP NULL DEFAULT NULL,
    `views` INT(11) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_blog_slug` (`slug`),
    INDEX `idx_blog_status` (`status`),
    INDEX `idx_blog_category` (`category`),
    INDEX `idx_blog_featured` (`is_featured`),
    INDEX `idx_blog_published` (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- 10. Newsletter Subscribers Table
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `name` VARCHAR(100) DEFAULT NULL,
    `status` ENUM('active', 'unsubscribed') NOT NULL DEFAULT 'active',
    `subscribed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `unsubscribed_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_newsletter_email` (`email`),
    INDEX `idx_newsletter_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- ADD NEW COLUMNS TO EXISTING BOOKINGS TABLE
-- -------------------------------------------
ALTER TABLE `bookings` 
ADD COLUMN IF NOT EXISTS `urgency` ENUM('low', 'medium', 'high', 'emergency') NOT NULL DEFAULT 'medium' AFTER `preferred_time`,
ADD COLUMN IF NOT EXISTS `estimated_budget` DECIMAL(10,2) DEFAULT NULL AFTER `urgency`,
ADD COLUMN IF NOT EXISTS `quantity` INT(3) NOT NULL DEFAULT 1 AFTER `estimated_budget`,
ADD COLUMN IF NOT EXISTS `preferred_contact_time` VARCHAR(50) DEFAULT NULL AFTER `quantity`,
ADD COLUMN IF NOT EXISTS `payment_required` TINYINT(1) NOT NULL DEFAULT 0 AFTER `preferred_contact_time`,
ADD COLUMN IF NOT EXISTS `payment_status` ENUM('none', 'pending', 'paid', 'failed') NOT NULL DEFAULT 'none' AFTER `payment_required`;

-- -------------------------------------------
-- PHP Sessions (shared across serverless invocations)
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `php_sessions` (
    `session_id` VARCHAR(128) NOT NULL,
    `session_data` MEDIUMBLOB NOT NULL,
    `expires_at` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`session_id`),
    INDEX `idx_php_sessions_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- SAMPLE DATA FOR NEW TABLES
-- -------------------------------------------

-- Sample Testimonials
INSERT INTO `testimonials` (`client_name`, `client_designation`, `client_company`, `rating`, `testimonial_text`, `service_id`, `display_order`, `status`) VALUES
('Rajesh Patel', 'CEO', 'Patel Enterprises', 5, 'Khodiyar Computer transformed our online presence completely. Their digital marketing strategies doubled our leads within 3 months. Highly recommended!', 1, 1, 'active'),
('Priya Sharma', 'Founder', 'Sharma Designs', 5, 'The website they designed for us is absolutely stunning. Professional team with excellent attention to detail. Great experience working with them.', 2, 2, 'active'),
('Amit Shah', 'Director', 'Shah Technologies', 4, 'Very reliable computer repair service. They fixed my laptop within hours and the price was very reasonable. Will definitely use their services again.', 7, 3, 'active'),
('Sneha Mehta', 'Marketing Head', 'Mehta Group', 5, 'Our social media engagement increased by 300% after Khodiyar Computer took over our social media handling. Their content is creative and engaging.', 6, 4, 'active'),
('Vikram Joshi', 'Owner', 'Joshi Retail', 5, 'Excellent SEO services! Our website now ranks on first page for all our target keywords. The team is knowledgeable and professional.', 5, 5, 'active');

-- Sample Blog Post
INSERT INTO `blog_posts` (`title`, `slug`, `category`, `tags`, `excerpt`, `content`, `author`, `is_featured`, `status`, `published_at`) VALUES
('Top 10 Digital Marketing Trends in 2026', 'digital-marketing-trends-2026', 'Digital Marketing', 'marketing,trends,2026,digital', 'Stay ahead of the competition with these game-changing digital marketing trends that are shaping 2026.', '<h2>Introduction</h2><p>The digital marketing landscape is evolving faster than ever. As we move through 2026, businesses need to adapt to new technologies and changing consumer behaviors.</p><h2>1. AI-Powered Marketing</h2><p>Artificial Intelligence is revolutionizing how we approach marketing. From personalized content to predictive analytics, AI tools are becoming essential for modern marketers.</p><h2>2. Voice Search Optimization</h2><p>With the rise of smart speakers and voice assistants, optimizing for voice search is no longer optional. Focus on natural language and long-tail keywords.</p><h2>3. Video Content Dominance</h2><p>Short-form video continues to dominate social media. Platforms like Instagram Reels and YouTube Shorts are essential for brand visibility.</p><h2>4. Social Commerce</h2><p>Shopping directly through social media platforms is becoming the norm. Integrate your products with social commerce features.</p><h2>5. Sustainability Marketing</h2><p>Consumers care about the environment. Brands that demonstrate genuine sustainability efforts gain customer loyalty.</p><h2>Conclusion</h2><p>Stay adaptable and keep learning. The digital marketing world never stops changing, and neither should your strategy.</p>', 'Khodiyar Computer Team', 1, 'published', NOW());

-- Newsletter sample subscriber
INSERT INTO `newsletter_subscribers` (`email`, `name`, `status`) VALUES
('info@khodiyarcomputer.com', 'Khodiyar Computer', 'active');

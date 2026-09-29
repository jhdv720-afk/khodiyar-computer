-- ============================================================
-- KHODIYAR COMPUTER - Database Schema
-- MariaDB 10.4.32 (MySQL Compatible)
-- ============================================================

CREATE DATABASE IF NOT EXISTS `khodiyar_computer` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `khodiyar_computer`;

-- -------------------------------------------
-- 1. Users Table (Frontend Website Users)
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `address` TEXT DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT NULL,
    `state` VARCHAR(100) DEFAULT NULL,
    `pincode` VARCHAR(10) DEFAULT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_email` (`email`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- 2. Admins Table (Admin Panel Users)
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `role` ENUM('super_admin', 'admin') NOT NULL DEFAULT 'admin',
    `profile_image` VARCHAR(255) DEFAULT NULL,
    `last_login` TIMESTAMP NULL DEFAULT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_username` (`username`),
    INDEX `idx_admin_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- 3. Services Table
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `services` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(200) NOT NULL UNIQUE,
    `short_desc` VARCHAR(300) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `icon` VARCHAR(100) DEFAULT 'fas fa-cog',
    `image` VARCHAR(255) DEFAULT NULL,
    `price_range` VARCHAR(50) DEFAULT NULL,
    `features` TEXT DEFAULT NULL,
    `display_order` INT(3) NOT NULL DEFAULT 0,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_slug` (`slug`),
    INDEX `idx_service_status` (`status`),
    INDEX `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- 4. Bookings Table
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `bookings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `booking_id` VARCHAR(20) NOT NULL UNIQUE,
    `user_id` INT(11) DEFAULT NULL,
    `service_id` INT(11) NOT NULL,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_email` VARCHAR(100) NOT NULL,
    `customer_phone` VARCHAR(20) NOT NULL,
    `customer_address` TEXT DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `preferred_date` DATE NOT NULL,
    `preferred_time` TIME DEFAULT NULL,
    `status` ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'rejected') NOT NULL DEFAULT 'pending',
    `admin_notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_booking_id` (`booking_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_service_id` (`service_id`),
    INDEX `idx_booking_status` (`status`),
    INDEX `idx_booking_date` (`preferred_date`),
    CONSTRAINT `fk_booking_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_booking_service` FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- 5. Contacts Table
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `contacts` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `subject` VARCHAR(200) DEFAULT NULL,
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `replied` TINYINT(1) NOT NULL DEFAULT 0,
    `reply_message` TEXT DEFAULT NULL,
    `replied_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_contact_read` (`is_read`),
    INDEX `idx_contact_replied` (`replied`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- 6. Admin Settings Table
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------
-- 7. PHP Sessions (shared across serverless invocations)
-- -------------------------------------------
CREATE TABLE IF NOT EXISTS `php_sessions` (
    `session_id` VARCHAR(128) NOT NULL,
    `session_data` MEDIUMBLOB NOT NULL,
    `expires_at` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`session_id`),
    INDEX `idx_php_sessions_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INSERT DEFAULT DATA
-- ============================================================

-- Default Admin (password: admin123)
INSERT INTO `admins` (`username`, `email`, `password`, `full_name`, `role`) VALUES
('admin', 'admin@khodiyarcomputer.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin', 'super_admin');

-- Default Services
INSERT INTO `services` (`title`, `slug`, `short_desc`, `description`, `icon`, `features`, `price_range`) VALUES
('Digital Marketing', 'digital-marketing', 'Boost your online presence with targeted digital marketing strategies.', '<p>Our digital marketing services help businesses reach their target audience effectively. We use data-driven strategies to maximize ROI and grow your brand presence online.</p><p>From social media campaigns to email marketing, we cover all aspects of digital promotion with measurable results.</p>', 'fas fa-chart-line', '["Social Media Marketing", "Email Marketing", "PPC Advertising", "Content Marketing", "Influencer Marketing", "Analytics & Reporting"]', '₹5,000 - ₹50,000'),
('Website Designing', 'website-designing', 'Create stunning, user-friendly websites that captivate your audience.', '<p>We design modern, responsive websites that provide an exceptional user experience. Our designs are tailored to your brand identity and business goals.</p><p>From wireframes to final delivery, we ensure pixel-perfect designs that work seamlessly across all devices.</p>', 'fas fa-paint-brush', '["UI/UX Design", "Responsive Design", "Wireframing", "Prototyping", "Landing Pages", "E-commerce Design"]', '₹8,000 - ₹80,000'),
('Graphic Designing', 'graphic-designing', 'Eye-catching visuals that communicate your brand story effectively.', '<p>Our graphic design services bring your brand to life through compelling visuals. We create designs that resonate with your audience and leave a lasting impression.</p><p>From logos to complete brand identity packages, we deliver creative excellence.</p>', 'fas fa-pen-fancy', '["Logo Design", "Brand Identity", "Brochure Design", "Social Media Graphics", "Business Cards", "Banner Design"]', '₹2,000 - ₹25,000'),
('Web Development', 'web-development', 'Powerful, scalable web applications built with modern technologies.', '<p>We build robust web applications using cutting-edge technologies. Our development team ensures clean code, optimal performance, and scalable architecture.</p><p>From simple websites to complex web applications, we deliver solutions that drive business growth.</p>', 'fas fa-code', '["Custom Web Apps", "CMS Development", "E-commerce Solutions", "API Integration", "Database Design", "Performance Optimization"]', '₹15,000 - ₹2,00,000'),
('SEO Optimization', 'seo-optimization', 'Improve your search rankings and drive organic traffic to your website.', '<p>Our SEO strategies help your website rank higher on search engines, driving qualified organic traffic. We follow white-hat techniques that deliver sustainable results.</p><p>Comprehensive SEO audits, keyword research, and ongoing optimization are part of our service.</p>', 'fas fa-search', '["Keyword Research", "On-Page SEO", "Off-Page SEO", "Technical SEO", "Content Strategy", "SEO Audit"]', '₹4,000 - ₹40,000'),
('Social Media Handling', 'social-media-handling', 'Manage and grow your social media presence with engaging content.', '<p>We take care of your social media presence so you can focus on your business. Our team creates, schedules, and manages content across all major platforms.</p><p>From content creation to community management, we help you build a strong social media presence.</p>', 'fas fa-share-alt', '["Content Creation", "Post Scheduling", "Community Management", "Social Analytics", "Ad Campaigns", "Influencer Outreach"]', '₹3,000 - ₹30,000'),
('Computer Repairing', 'computer-repairing', 'Professional computer repair and maintenance services at your doorstep.', '<p>Our expert technicians provide reliable computer repair services for both hardware and software issues. We diagnose problems accurately and fix them efficiently.</p><p>From virus removal to hardware upgrades, we offer comprehensive computer repair solutions.</p>', 'fas fa-tools', '["Hardware Repair", "Software Installation", "Virus Removal", "Data Recovery", "System Upgrade", "Network Setup"]', '₹500 - ₹15,000');

-- Default Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('company_name', 'Khodiyar Computer'),
('company_email', 'info@khodiyarcomputer.com'),
('company_phone', '+91 98765 43210'),
('company_address', '123, Business Hub, Near Main Market, Rajkot - 360001, Gujarat, India'),
('company_tagline', 'Your Trusted IT Solutions Partner'),
('working_hours', 'Mon - Sat: 9:00 AM - 8:00 PM'),
('facebook_url', 'https://facebook.com/khodiyarcomputer'),
('instagram_url', 'https://instagram.com/khodiyarcomputer'),
('twitter_url', 'https://twitter.com/khodiyarcomp'),
('linkedin_url', 'https://linkedin.com/company/khodiyarcomputer'),
('whatsapp_number', '+919876543210'),
('about_short', 'Khodiyar Computer is a leading IT services company based in Rajkot, Gujarat. We provide comprehensive digital solutions including web development, digital marketing, graphic design, and computer repair services.'),
('about_long', 'Founded with a vision to empower businesses through technology, Khodiyar Computer has been delivering excellence in IT services since our inception. Our team of experienced professionals is dedicated to providing innovative solutions that drive business growth.\n\nWe believe in building long-term relationships with our clients through trust, transparency, and exceptional service quality. Our commitment to staying updated with the latest technologies ensures that our clients always get the best solutions.'),
('footer_text', 'Empowering businesses with cutting-edge IT solutions. Your success is our priority.'),
('meta_description', 'Khodiyar Computer - Your trusted IT services partner in Rajkot. We offer Digital Marketing, Web Development, Graphic Design, SEO, and Computer Repair services.');

-- Sample User (password: user123)
INSERT INTO `users` (`full_name`, `email`, `phone`, `password`, `address`, `city`, `state`, `pincode`) VALUES
('Test User', 'user@test.com', '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '123, Test Address', 'Rajkot', 'Gujarat', '360001');


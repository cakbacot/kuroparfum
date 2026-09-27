USE `kuro_ecommerce`;

-- ===================================================
-- PATCH: Auction System & Dynamic Series Categories
-- MariaDB compatible - Run after patch_series24.sql
-- ===================================================

-- 1. Extend products table with auction and series columns (idempotent)
ALTER TABLE `products`
    ADD COLUMN IF NOT EXISTS `series_category` VARCHAR(50) NULL AFTER `edition_type`,
    ADD COLUMN IF NOT EXISTS `initial_stock` INT NULL AFTER `stock`,
    ADD COLUMN IF NOT EXISTS `is_auction` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`,
    ADD COLUMN IF NOT EXISTS `auction_status` ENUM('upcoming','open','closed','cancelled') DEFAULT NULL AFTER `is_auction`,
    ADD COLUMN IF NOT EXISTS `auction_start_price` DECIMAL(15,2) DEFAULT NULL AFTER `auction_status`,
    ADD COLUMN IF NOT EXISTS `auction_increment` DECIMAL(15,2) DEFAULT NULL AFTER `auction_start_price`,
    ADD COLUMN IF NOT EXISTS `auction_end_time` DATETIME DEFAULT NULL AFTER `auction_increment`,
    ADD COLUMN IF NOT EXISTS `auction_winner_id` INT DEFAULT NULL AFTER `auction_end_time`,
    ADD COLUMN IF NOT EXISTS `auction_winning_bid` DECIMAL(15,2) DEFAULT NULL AFTER `auction_winner_id`;

-- Extend edition_type enum to include 'auction_ticket'
ALTER TABLE `products`
    MODIFY COLUMN `edition_type` ENUM(
        '1-of-1',
        'Limited Reserve',
        'Series-24',
        'auction_ticket'
    ) DEFAULT 'Series-24';

-- 2. Auction Bids Table
CREATE TABLE IF NOT EXISTS `auction_bids` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `user_id`    INT NOT NULL,
    `bid_amount` DECIMAL(15,2) NOT NULL,
    `bid_at`     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE CASCADE,
    INDEX `idx_product_bid` (`product_id`, `bid_amount`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Auction Access Table (ticket grants access to a specific auction)
CREATE TABLE IF NOT EXISTS `auction_access` (
    `id`              INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`         INT NOT NULL,
    `product_id`      INT NOT NULL COMMENT 'The auction product this access is for',
    `ticket_order_id` INT NULL     COMMENT 'order_id that granted this access',
    `granted_at`      DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_auction` (`user_id`, `product_id`),
    FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Add fulfillment & shipping columns to orders
ALTER TABLE `orders`
    ADD COLUMN IF NOT EXISTS `fulfillment_status`       ENUM('awaiting_payment','processing','packing','shipped','delivered','cancelled') DEFAULT 'awaiting_payment' AFTER `payment_status`,
    ADD COLUMN IF NOT EXISTS `shipping_courier`          VARCHAR(100) NULL AFTER `fulfillment_status`,
    ADD COLUMN IF NOT EXISTS `shipping_tracking_number`  VARCHAR(150) NULL AFTER `shipping_courier`,
    ADD COLUMN IF NOT EXISTS `admin_note`                TEXT NULL    AFTER `shipping_tracking_number`,
    ADD COLUMN IF NOT EXISTS `is_auction_order`          TINYINT(1) DEFAULT 0 AFTER `admin_note`,
    ADD COLUMN IF NOT EXISTS `delivered_at`              DATETIME NULL AFTER `acquired_at`;

-- 5. Computed view: auto-categorises products by stock level
CREATE OR REPLACE VIEW `product_series_view` AS
SELECT
    p.id,
    p.slug,
    p.name,
    p.subtitle,
    p.japanese_name,
    p.edition_type,
    p.edition_serial,
    p.price,
    p.stock,
    p.initial_stock,
    p.volume_ml,
    p.concentration,
    p.description,
    p.philosophy,
    p.flacon_craftsmanship,
    p.image_url,
    p.status,
    p.is_auction,
    p.auction_status,
    p.auction_start_price,
    p.auction_increment,
    p.auction_end_time,
    p.auction_winner_id,
    p.auction_winning_bid,
    p.created_at,
    CASE
        WHEN p.edition_type = 'auction_ticket' THEN 'Auction Ticket'
        WHEN p.is_auction = 1                   THEN 'Auction'
        WHEN p.series_category IS NOT NULL AND p.series_category != '' THEN p.series_category
        WHEN p.edition_type = '1-of-1' OR p.stock = 1 THEN 'Limited Edition'
        WHEN COALESCE(p.initial_stock, p.stock) <= 8  THEN 'Premium Series'
        WHEN COALESCE(p.initial_stock, p.stock) <= 12 THEN 'Deluxe Series'
        ELSE                                               'Reguler Series'
    END AS `series_category`,
    CASE
        WHEN p.is_auction = 1  THEN 0
        WHEN p.series_category = 'Limited Edition' OR p.edition_type = '1-of-1' OR p.stock = 1 THEN 1
        WHEN p.series_category = 'Premium Series' OR COALESCE(p.initial_stock, p.stock) <= 8 THEN 2
        WHEN p.series_category = 'Deluxe Series'  OR COALESCE(p.initial_stock, p.stock) <= 12 THEN 3
        ELSE                                                                                   4
    END AS `series_rank`
FROM `products` p;
